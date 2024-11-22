<?php
ob_start(); // Start output buffering
// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
require_once 'Database.php';
require 'logger.php';
session_start();

// Generate CSRF token if it doesn't already exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_token'];
$database = new Database();
$logger = new Logger();
$conn = $database->connect();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quotation'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token.');
    }

    $quotation_errors = [];
    $form_data = [];

    // Sanitize inputs and collect errors
    $form_data['full_names'] = htmlspecialchars(trim($_POST['full_names'] ?? ''), ENT_QUOTES, 'UTF-8');
    if (empty($form_data['full_names'])) {
        $quotation_errors['full_names'] = 'Full Names are required.';
    } elseif (strlen($form_data['full_names']) < 1 || strlen($form_data['full_names']) > 255) {
        $quotation_errors['full_names'] = 'Full Names must be between 1 and 255 characters.';
    }

    $form_data['email'] = htmlspecialchars(trim($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8');
    if (empty($form_data['email']) || !filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $quotation_errors['email'] = 'A valid email address is required.';
    } elseif (strlen($form_data['email']) > 255) {
        $quotation_errors['email'] = 'Email must be less than 255 characters.';
    }

    $form_data['county'] = htmlspecialchars(trim($_POST['county'] ?? ''), ENT_QUOTES, 'UTF-8');
    if (empty($form_data['county'])) {
        $quotation_errors['county'] = 'County is required.';
    }

    $form_data['city'] = htmlspecialchars(trim($_POST['city'] ?? ''), ENT_QUOTES, 'UTF-8');
    if (empty($form_data['city'])) {
        $quotation_errors['city'] = 'City is required.';
    }

    $form_data['phone_number'] = htmlspecialchars(trim($_POST['phone_number'] ?? ''), ENT_QUOTES, 'UTF-8');
    if (empty($form_data['phone_number'])) {
        $quotation_errors['phone_number'] = 'Phone number is required.';
    }

    $form_data['id_number'] = htmlspecialchars(trim($_POST['id_number'] ?? ''), ENT_QUOTES, 'UTF-8');
    if (empty($form_data['id_number'])) {
        $quotation_errors['id_number'] = 'ID number is required.';
    }

    $form_data['date_of_birth'] = htmlspecialchars($_POST['date_of_birth'] ?? '', ENT_QUOTES, 'UTF-8');
    if (empty($form_data['date_of_birth']) || !strtotime($form_data['date_of_birth'])) {
        $quotation_errors['date_of_birth'] = 'A valid Date of Birth is required.';
    }

    $form_data['gender'] = htmlspecialchars(trim($_POST['gender'] ?? ''), ENT_QUOTES, 'UTF-8');
    if (empty($form_data['gender'])) {
        $quotation_errors['gender'] = 'Gender is required.';
    }

    $form_data['age'] = filter_var($_POST['age'] ?? null, FILTER_VALIDATE_INT);
    if ($form_data['age'] === false || $form_data['age'] < 1) {
        $quotation_errors['age'] = 'Age must be a positive integer.';
    }

    $form_data['house_type'] = htmlspecialchars($_POST['house_type'] ?? '', ENT_QUOTES, 'UTF-8');
    if (empty($form_data['house_type'])) {
        $quotation_errors['house_type'] = 'House type is required.';
    }

    $form_data['no_of_beds'] = filter_var($_POST['no_of_beds'] ?? null, FILTER_VALIDATE_INT);
    if ($form_data['no_of_beds'] === false || $form_data['no_of_beds'] < 1 || $form_data['no_of_beds'] > 5) {
        $quotation_errors['no_of_beds'] = 'Number of beds must be between 1 and 5.';
    }

    $form_data['no_of_baths'] = filter_var($_POST['no_of_baths'] ?? null, FILTER_VALIDATE_INT);
    if ($form_data['no_of_baths'] === false || $form_data['no_of_baths'] < 1 || $form_data['no_of_baths'] > 5) {
        $quotation_errors['no_of_baths'] = 'Number of baths must be between 1 and 5.';
    }

    // If there are errors, redirect back to the form
    if (!empty($quotation_errors)) {
        // Store errors and form data in session
        $_SESSION['quotation_errors'] = $quotation_errors;
        $_SESSION['form_data'] = $form_data;

        // Log the errors for debugging
        $logger->logMessage('Form submission errors: ' . json_encode($quotation_errors), 'ERROR');

        // Redirect back to the form
        header('Location: index.php');
        exit();
    }

    // Process the form if no errors
    $quotation_UUID = bin2hex(random_bytes(16));
    $current_timestamp = date('Y-m-d H:i:s');

    $quotation_query = "INSERT INTO quotations
        (quotation_UUID, full_names, email, county_id, city, town, phone_number, id_number, date_of_birth, gender_id, age, house_type_id, no_of_beds, no_of_baths, created_at, updated_at)
        VALUES
        (:quotation_UUID, :full_names, :email, :county_id, :city, :town, :phone_number, :id_number, :date_of_birth, :gender_id, :age, :house_type_id, :no_of_beds, :no_of_baths, :created_at, :updated_at)";

    try {
        $quotation_statement = $conn->prepare($quotation_query);

        // Bind parameters
        $quotation_statement->bindParam(':quotation_UUID', $quotation_UUID);
        $quotation_statement->bindParam(':full_names', $form_data['full_names']);
        $quotation_statement->bindParam(':email', $form_data['email']);
        $quotation_statement->bindParam(':county_id', $form_data['county']);
        $quotation_statement->bindParam(':city', $form_data['city']);
        $quotation_statement->bindParam(':town', $form_data['town']);
        $quotation_statement->bindParam(':phone_number', $form_data['phone_number']);
        $quotation_statement->bindParam(':id_number', $form_data['id_number']);
        $quotation_statement->bindParam(':date_of_birth', $form_data['date_of_birth']);
        $quotation_statement->bindParam(':gender_id', $form_data['gender']);
        $quotation_statement->bindParam(':age', $form_data['age']);
        $quotation_statement->bindParam(':house_type_id', $form_data['house_type']);
        $quotation_statement->bindParam(':no_of_beds', $form_data['no_of_beds']);
        $quotation_statement->bindParam(':no_of_baths', $form_data['no_of_baths']);
        $quotation_statement->bindParam(':created_at', $current_timestamp);
        $quotation_statement->bindParam(':updated_at', $current_timestamp);

        // Log query execution attempt
        // $logger->logMessage('Executing database insert for quotation UUID: ' . $quotation_UUID, 'INFO');

        if ($quotation_statement->execute()) {
            $_SESSION['status'] = 'SUCCESS';
            $_SESSION['message'] = 'Quotation requested successfully!';
            $logger->logMessage('Quotation request successfully inserted into database.', 'INFO'); // Log success
        } else {
            $_SESSION['status'] = 'ERROR';
            $_SESSION['message'] = 'Could not request the quotation.';
            $logger->logMessage('Failed to insert quotation request into database.', 'ERROR'); // Log failure
        }
    } catch (PDOException $e) {
        $logger->logException($e); // Log the exception details
        error_log("Database Insert Error: " . $e->getMessage());
        $_SESSION['status'] = 'ERROR';
        $_SESSION['message'] = 'An error occurred. Please try again.';
    }

    // Close the connection AFTER executing the query
    $conn = null;

    // Redirect after successful submission
    header('Location: index.php');
    exit();
}

ob_end_flush(); // End output buffering



