<?php
ob_start(); // Start output buffering
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);
require_once 'Database.php';
require_once 'TokenManager.php';
require 'logger.php';
session_start();

// Generate CSRF token if it doesn't already exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_token'];
$database = new Database();
$tokenManager = new TokenManager();
$logger = new Logger();
$conn = $database->connect();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_registration'])) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token.');
    }

    $registration_errors = [];
    $form_data = [];

    // Sanitize inputs and collect errors
    $form_data['first_name'] = htmlspecialchars(trim($_POST['first_name'] ?? ''), ENT_QUOTES, 'UTF-8');
    if (empty($form_data['first_name'])) {
        $registration_errors['first_name'] = 'First Name is required.';
    } elseif (strlen($form_data['first_name']) < 1) {
        $registration_errors['first_name'] = 'First Name should be greater than 1 character in length.';
    } elseif (strlen($form_data['first_name']) > 255) {
        $registration_errors['first_name'] = 'First Name should be less than 255 characters in length.';
    }

    $form_data['middle_name'] = htmlspecialchars(trim($_POST['middle_name'] ?? ''), ENT_QUOTES, 'UTF-8');
    if (empty($form_data['middle_name'])) {
        $registration_errors['middle_name'] = 'Middle Name is required.';
    } elseif (strlen($form_data['middle_name']) < 1) {
        $registration_errors['middle_name'] = 'Middle Name should be greater than 1 character in length.';
    } elseif (strlen($form_data['middle_name']) > 255) {
        $registration_errors['middle_name'] = 'Middle Name should be less than 255 characters in length.';
    }

    $form_data['surname'] = htmlspecialchars(trim($_POST['surname'] ?? ''), ENT_QUOTES, 'UTF-8');
    if (empty($form_data['surname'])) {
        $registration_errors['surname'] = 'Surname is required.';
    } elseif (strlen($form_data['surname']) < 1) {
        $registration_errors['surname'] = 'Surname should be greater than 1 character in length.';
    } elseif (strlen($form_data['surname']) > 255) {
        $registration_errors['surname'] = 'Surname should be less than 255 characters in length.';
    }

    $form_data['id_number'] = htmlspecialchars(trim($_POST['id_number'] ?? ''), ENT_QUOTES, 'UTF-8');
    if (empty($form_data['id_number'])) {
        $registration_errors['id_number'] = 'ID number is required.';
    } elseif (strlen($form_data['id_number']) < 1) {
        $registration_errors['id_number'] = 'ID number should be greater than 1 character in length.';
    } elseif (strlen($form_data['id_number']) > 55) {
        $registration_errors['id_number'] = 'ID number should be less than 55 characters in length.';
    }

    $form_data['kra_pin'] = htmlspecialchars(trim($_POST['kra_pin'] ?? ''), ENT_QUOTES, 'UTF-8');
    if (empty($form_data['kra_pin'])) {
        $registration_errors['kra_pin'] = 'KRA Pin is required.';
    } elseif (strlen($form_data['kra_pin']) < 1) {
        $registration_errors['kra_pin'] = 'KRA Pin should be greater than 1 character in length.';
    } elseif (strlen($form_data['kra_pin']) > 55) {
        $registration_errors['kra_pin'] = 'KRA Pin should be less than 55 characters in length.';
    }

    $form_data['email'] = htmlspecialchars(trim($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8');
    if (empty($form_data['email']) || !filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $registration_errors['email'] = 'Invalid email address.';
    } elseif (strlen($form_data['email']) < 1) {
        $registration_errors['email'] = 'Email should be greater than 1 character in length.';
    } elseif (strlen($form_data['email']) > 255) {
        $registration_errors['email'] = 'Email should be less than 255 characters in length.';
    }

    $form_data['city'] = htmlspecialchars(trim($_POST['city'] ?? ''), ENT_QUOTES, 'UTF-8');
    if (empty($form_data['city'])) {
        $registration_errors['city'] = 'City is required.';
    } elseif (strlen($form_data['city']) < 1) {
        $registration_errors['city'] = 'City should be greater than 1 character in length.';
    } elseif (strlen($form_data['city']) > 255) {
        $registration_errors['city'] = 'City should be less than 255 characters in length.';
    }

    $form_data['county'] = htmlspecialchars(trim($_POST['county'] ?? ''), ENT_QUOTES, 'UTF-8');
    if (empty($form_data['county'])) {
        $registration_errors['county'] = 'County is required.';
    } elseif (strlen($form_data['county']) < 1) {
        $registration_errors['county'] = 'County should be greater than 1 character in length.';
    } elseif (strlen($form_data['county']) > 255) {
        $registration_errors['county'] = 'County should be less than 255 characters in length.';
    }

    // Sanitize inputs and collect errors
    // $form_data['town'] = htmlspecialchars(trim($_POST['town'] ?? ''), ENT_QUOTES, 'UTF-8');
    // if (empty($form_data['town'])) {
    //     $registration_errors['town'] = 'Town is required.';
    // } elseif (strlen($form_data['town']) < 1) {
    //     $registration_errors['town'] = 'Town should be greater than 1 character in length.';
    // } elseif (strlen($form_data['town']) > 255) {
    //     $registration_errors['town'] = 'Town should be less than 255 characters in length.';
    // }

    $form_data['member_number'] = htmlspecialchars(trim($_POST['member_number'] ?? ''), ENT_QUOTES, 'UTF-8');
    if (empty($form_data['member_number'])) {
        $registration_errors['member_number'] = 'Member Number is required.';
    } elseif (strlen($form_data['member_number']) < 1) {
        $registration_errors['member_number'] = 'Member Number should be greater than 1 character in length.';
    } elseif (strlen($form_data['member_number']) > 255) {
        $registration_errors['member_number'] = 'Member Number should be less than 255 characters in length.';
    }

    $form_data['phone_number'] = htmlspecialchars(trim($_POST['phone_number'] ?? ''), ENT_QUOTES, 'UTF-8');
    if (empty($form_data['phone_number'])) {
        $registration_errors['phone_number'] = 'Phone number is required.';
    } elseif (strlen($form_data['phone_number']) < 8) {
        $registration_errors['phone_number'] = 'Phone number should be greater than 8 characters in length.';
    } elseif (strlen($form_data['phone_number']) > 15) {
        $registration_errors['phone_number'] = 'Phone number should be less than 15 characters in length.';
    }

    $form_data['id_number'] = htmlspecialchars(trim($_POST['id_number'] ?? ''), ENT_QUOTES, 'UTF-8');
    if (empty($form_data['id_number'])) {
        $registration_errors['id_number'] = 'ID number is required.';
    } elseif (strlen($form_data['id_number']) < 1) {
        $registration_errors['id_number'] = 'ID number should be greater than 1 character in length.';
    } elseif (strlen($form_data['id_number']) > 55) {
        $registration_errors['id_number'] = 'ID number should be less than 55 characters in length.';
    }

    $form_data['date_of_birth'] = htmlspecialchars(trim($_POST['date_of_birth'] ?? ''), ENT_QUOTES, 'UTF-8');
    if (empty($form_data['date_of_birth'])) {
        $registration_errors['date_of_birth'] = 'Date of birth is required.';
    } elseif (!empty($form_data['date_of_birth']) && !strtotime($form_data['date_of_birth'])) {
        $registration_errors['date_of_birth'] = 'Invalid date of birth.';
    }

    $form_data['age'] = htmlspecialchars(trim($_POST['age'] ?? ''), ENT_QUOTES, 'UTF-8');
    if (empty($form_data['age'])) {
        $registration_errors['age'] = 'Age is required.';
    }

    $form_data['payment_plan'] = htmlspecialchars(trim($_POST['payment_plan'] ?? ''), ENT_QUOTES, 'UTF-8');
    if (empty($form_data['payment_plan'])) {
        $registration_errors['payment_plan'] = 'Payment plan is required.';
    }

    $form_data['mortgage_plan'] = htmlspecialchars(trim($_POST['mortgage_plan'] ?? ''), ENT_QUOTES, 'UTF-8');
    if (empty($form_data['mortgage_plan'])) {
        $registration_errors['mortgage_plan'] = 'Mortgage plan is required.';
    }

    $form_data['gender'] = htmlspecialchars(trim($_POST['gender'] ?? ''), ENT_QUOTES, 'UTF-8');
    if (empty($form_data['gender'])) {
        $registration_errors['gender'] = 'Gender is required.';
    }

    $form_data['relationship_status'] = htmlspecialchars(trim($_POST['relationship_status'] ?? ''), ENT_QUOTES, 'UTF-8');
    if (empty($form_data['relationship_status'])) {
        $registration_errors['relationship_status'] = 'Relationship status is required.';
    }

    // $form_data['promotional_emails'] = htmlspecialchars(trim($_POST['promotional_emails'] ?? ''), ENT_QUOTES, 'UTF-8');
    // if (empty($form_data['promotional_emails'])) {
    //     $registration_errors['promotional_emails'] = 'Promotional emails is required.';
    // }

    // $form_data['exclusive_emails'] = htmlspecialchars(trim($_POST['exclusive_emails'] ?? ''), ENT_QUOTES, 'UTF-8');
    // if (empty($form_data['exclusive_emails'])) {
    //     $registration_errors['exclusive_emails'] = 'Exclusive emails is required.';
    // }

    // If there are errors, redirect back to the form
    if (!empty($registration_errors)) {
        // Store errors and form data in session
        $_SESSION['registration_errors'] = $registration_errors;
        $_SESSION['form_data'] = $form_data;

        // Log the errors for debugging
        $logger->logMessage('Form submission errors: ' . json_encode($registration_errors), 'ERROR');

        // Redirect back to the form
        header('Location: index.php');
        exit();
    }

    require_once 'KCB_BUNI_STK_PUSH.php';
    require_once 'Response.php';
    $mpesaAPI = new MpesaAPI();
    $response = new Response();

    $registration_UUID = $tokenManager->generateUUIDv4();
    $current_timestamp = date('Y-m-d H:i:s');

    $registration_query = "INSERT INTO registrations
        (registration_UUID, first_name, middle_name, surname, id_number, kra_pin, city, county_id, town, date_of_birth, age, email, member_number, phone_number, pymnt_pln_id, mtge_pln_id, gender_id, rlshp_sts_id, promotional_emails, exclusive_emails, created_at, updated_at)
        VALUES
        (:registration_UUID, :first_name, :middle_name, :surname, :id_number, :kra_pin, :city, :county_id, :town, :date_of_birth, :age, :email, :member_number, :phone_number, :pymnt_pln_id, :mtge_pln_id, :gender_id, :rlshp_sts_id, :promotional_emails, :exclusive_emails, :created_at, :updated_at)";

    try {
        $registration_statement = $conn->prepare($registration_query);

        $registration_statement->bindParam(':registration_UUID', $registration_UUID);
        $registration_statement->bindParam(':first_name', $form_data['first_name']);
        $registration_statement->bindParam(':middle_name', $form_data['middle_name']);
        $registration_statement->bindParam(':surname', $form_data['surname']);
        $registration_statement->bindParam(':id_number', $form_data['id_number']);
        $registration_statement->bindParam(':kra_pin', $form_data['kra_pin']);
        $registration_statement->bindParam(':county_id', $form_data['county']);
        $registration_statement->bindParam(':city', $form_data['city']);
        $registration_statement->bindParam(':town', $form_data['town']);
        $registration_statement->bindParam(':date_of_birth', $form_data['date_of_birth']);
        $registration_statement->bindParam(':age', $form_data['age']);
        $registration_statement->bindParam(':email', $form_data['email']);
        $registration_statement->bindParam(':member_number', $form_data['member_number']);
        $registration_statement->bindParam(':phone_number', $form_data['phone_number']);
        $registration_statement->bindParam(':pymnt_pln_id', $form_data['payment_plan']);
        $registration_statement->bindParam(':mtge_pln_id', $form_data['mortgage_plan']);
        $registration_statement->bindParam(':gender_id', $form_data['gender']);
        $registration_statement->bindParam(':rlshp_sts_id', $form_data['relationship_status']);
        $registration_statement->bindParam(':promotional_emails', $form_data['promotional_emails']);
        $registration_statement->bindParam(':exclusive_emails', $form_data['exclusive_emails']);
        $registration_statement->bindParam(':created_at', $current_timestamp);
        $registration_statement->bindParam(':updated_at', $current_timestamp);

        // Log query execution attempt
        // $logger->logMessage('Executing database insert for registration UUID: ' . $registration_UUID, 'INFO');

        if ($registration_statement->execute()) {
            $inputData = [
                "phoneNumber" => $form_data['phone_number'],
                "amount" => "1",
                "invoiceNumber" => "7869410#" . $response->randomInvoiceNumber(),
                "sharedShortCode" => true,
                "orgShortCode" => "",
                "orgPassKey" => "",
                "callbackUrl" => "https://c61e-80-240-201-162.ngrok-free.app/Projects/jengaPamoja/KBA/CallBackURL.php",
                // "callbackUrl" => "https://omohhomes.com/jengaPamoja/kba/CallBackURL.php", // Use this line in production
                "transactionDescription" => "Registration Fees"
            ];

            // Log query execution attempt with input data
            $logger->logMessage("[" . date("Y-m-d H:i:s") . "] Input Data: " . json_encode($inputData, JSON_PRETTY_PRINT));
            $mpesaAPI->handleRequest($inputData);

            // Set success message in the session
            $_SESSION['status'] = 'SUCCESS';
            $_SESSION['message'] = 'Registration was successful!';
            $logger->logMessage('Registration was successful.', 'INFO'); // Log success
        } else {
            // Get error information from the database statement
            $errorInfo = $registration_statement->errorInfo();
            $logger->logMessage("Failed to register. Error: " . $errorInfo[2], 'ERROR');

            // Set error message in the session
            $_SESSION['status'] = 'ERROR';
            $_SESSION['message'] = 'Could not register.';
            // echo "Execute Error: " . $errorInfo[2]; // Avoid showing detailed errors in production for security
        }
    } catch (PDOException $e) {
        $logger->logException($e); // Log the exception details
        error_log("Database Insert Error: " . $e->getMessage());
        $_SESSION['status'] = 'ERROR';
        $_SESSION['message'] = 'An error occurred. Please try again.';
    }

    // Close the connection AFTER executing the query
    $conn = null;

    // Redirect to the form page with success/error message
    // After processing, reload the page:
    header('Location: index.php');
    exit();
}

ob_end_flush(); // End output buffering



