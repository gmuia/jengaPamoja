<?php
ob_start(); // Start output buffering
require_once 'Database.php';
require_once 'TokenManager.php';
include 'logger.php';

session_start();
// Generate CSRF token if it doesn't already exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_token'];
$tokenManager = new TokenManager();
$database = new Database();

$conn = $database->connect();
$countries_query = "SELECT * FROM countries";
$house_types_query = "SELECT * FROM house_types";
$payment_plans_query = "SELECT * FROM payment_plans";
$mortgage_plans_query = "SELECT * FROM mortgage_plans";
$genders_query = "SELECT * FROM genders";
$relationship_statuses_query = "SELECT * FROM relationship_statuses";

try {
    $countries_statement = $conn->prepare($countries_query);
    $countries_statement->execute();
    $countries = $countries_statement->fetchAll(PDO::FETCH_ASSOC);

    $house_types_statement = $conn->prepare($house_types_query);
    $house_types_statement->execute();
    $house_types = $house_types_statement->fetchAll(PDO::FETCH_ASSOC);

    $payment_plans_statement = $conn->prepare($payment_plans_query);
    $payment_plans_statement->execute();
    $payment_plans = $payment_plans_statement->fetchAll(PDO::FETCH_ASSOC);

    $mortgage_plans_statement = $conn->prepare($mortgage_plans_query);
    $mortgage_plans_statement->execute();
    $mortgage_plans = $mortgage_plans_statement->fetchAll(PDO::FETCH_ASSOC);

    $genders_statement = $conn->prepare($genders_query);
    $genders_statement->execute();
    $genders = $genders_statement->fetchAll(PDO::FETCH_ASSOC);

    $relationship_statuses_statement = $conn->prepare($relationship_statuses_query);
    $relationship_statuses_statement->execute();
    $relationship_statuses = $relationship_statuses_statement->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Generate CSRF token if it doesn't already exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_registration'])) {
    // Initialize errors array for validation feedback
    $registration_errors = [];

    $first_name = htmlspecialchars(trim($_POST['first_name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $last_name = htmlspecialchars(trim($_POST['last_name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $surname = htmlspecialchars(trim($_POST['surname'] ?? ''), ENT_QUOTES, 'UTF-8');
    $id_number = htmlspecialchars(trim($_POST['id_number'] ?? ''), ENT_QUOTES, 'UTF-8');
    $kra_pin = htmlspecialchars(trim($_POST['kra_pin'] ?? ''), ENT_QUOTES, 'UTF-8');
    $country = htmlspecialchars(trim($_POST['country'] ?? ''), ENT_QUOTES, 'UTF-8');
    $province = htmlspecialchars(trim($_POST['province'] ?? ''), ENT_QUOTES, 'UTF-8');
    $city = htmlspecialchars(trim($_POST['city'] ?? ''), ENT_QUOTES, 'UTF-8');
    $town = htmlspecialchars(trim($_POST['town'] ?? ''), ENT_QUOTES, 'UTF-8');
    $occupation = htmlspecialchars(trim($_POST['occupation'] ?? ''), ENT_QUOTES, 'UTF-8');
    $date_of_birth = htmlspecialchars($_POST['date_of_birth'] ?? '', ENT_QUOTES, 'UTF-8');
    $age = (int)htmlspecialchars($_POST['age'] ?? '', ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars(trim($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8');
    $organization_name = htmlspecialchars(trim($_POST['organization_name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $no_of_beds = htmlspecialchars($_POST['no_of_beds'] ?? '', ENT_QUOTES, 'UTF-8');
    $no_of_baths = htmlspecialchars($_POST['no_of_baths'] ?? '', ENT_QUOTES, 'UTF-8');
    $phone_number = htmlspecialchars(trim($_POST['phone_number'] ?? ''), ENT_QUOTES, 'UTF-8');
    $payment_plan = htmlspecialchars(trim($_POST['payment_plan'] ?? ''), ENT_QUOTES, 'UTF-8');
    $mortgage_plan = htmlspecialchars(trim($_POST['mortgage_plan'] ?? ''), ENT_QUOTES, 'UTF-8');
    $gender = htmlspecialchars(trim($_POST['gender'] ?? ''), ENT_QUOTES, 'UTF-8');
    $relationship_status = htmlspecialchars(trim($_POST['relationship_status'] ?? ''), ENT_QUOTES, 'UTF-8');
    $postal_code = htmlspecialchars(trim($_POST['postal_code'] ?? ''), ENT_QUOTES, 'UTF-8');
    $zip_code = htmlspecialchars(trim($_POST['zip_code'] ?? ''), ENT_QUOTES, 'UTF-8');
    $promotional_emails = htmlspecialchars(trim($_POST['promotional_emails'] ?? ''), ENT_QUOTES, 'UTF-8');
    $exclusive_emails = htmlspecialchars(trim($_POST['exclusive_emails'] ?? ''), ENT_QUOTES, 'UTF-8');

    // Validate fields
    if (empty($first_name)) {
        $registration_errors['first_name'] = 'First Name is required.';
    } else {
        if(strlen($first_name) < 1) {
            $registration_errors['first_name'] = 'First Name should be greater than 1 character in length.';
        }

        if(strlen($first_name) > 255) {
            $registration_errors['first_name'] = 'First Name should be less than 255 characters in length.';
        }
    }

    if (empty($last_name)) {
        $registration_errors['last_name'] = 'Last Name is required.';
    } else {
        if(strlen($first_name) < 1) {
            $registration_errors['last_name'] = 'Last Name should be greater than 1 character in length.';
        }

        if(strlen($first_name) > 255) {
            $registration_errors['last_name'] = 'Last Name should be less than 255 characters in length.';
        }
    }

    if (empty($surname)) {
        $registration_errors['surname'] = 'Surname is required.';
    } else {
        if(strlen($surname) < 1) {
            $registration_errors['surname'] = 'Surname should be greater than 1 character in length.';
        }

        if(strlen($surname) > 255) {
            $registration_errors['surname'] = 'Surname should be less than 255 characters in length.';
        }
    }

    if (empty($id_number)) {
        $registration_errors['id_number'] = 'Id number is required.';
    } else {
        if(strlen($id_number) < 1) {
            $registration_errors['id_number'] = 'Id number should be greater than 1 character in length.';
        }

        if(strlen($id_number) > 55) {
            $registration_errors['id_number'] = 'Id number should be less than 55 characters in length.';
        }
    }

    if (empty($kra_pin)) {
        $registration_errors['kra_pin'] = 'KRA Pin is required.';
    } else {
        if(strlen($kra_pin) < 1) {
            $registration_errors['kra_pin'] = 'KRA Pin should be greater than 1 character in length.';
        }

        if(strlen($kra_pin) > 55) {
            $registration_errors['kra_pin'] = 'KRA Pin should be less than 55 characters in length.';
        }
    }
   
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $registration_errors['email'] = 'Invalid email address.';
    } else {
        if(strlen($email) < 1) {
            $registration_errors['email'] = 'Email should be greater than 1 character in length.';
        }

        if(strlen($email) > 255) {
            $registration_errors['email'] = 'Email should be less than 255 characters in length.';
        }
    }

    if (empty($country)) {
        $registration_errors['country'] = 'Country is required.';
    }

    // if (empty($province)) {
    //     $registration_errors['province'] = 'Province is required.';
    // } else {
    //     if(strlen($full_names) < 1) {
    //         $registration_errors['province'] = 'Province should be greater than 1 character in length.';
    //     }

    //     if(strlen($full_names) > 255) {
    //         $registration_errors['province'] = 'Province should be less than 255 characters in length.';
    //     }
    // }

    if (empty($city)) {
        $registration_errors['city'] = 'City is required.';
    } else {
        if(strlen($city) < 1) {
            $registration_errors['city'] = 'City should be greater than 1 character in length.';
        }

        if(strlen($city) > 255) {
            $registration_errors['city'] = 'City should be less than 255 characters in length.';
        }
    }

    // if (empty($town)) {
    //     $registration_errors['town'] = 'Town is required.';
    // } else {
    //     if(strlen($town) < 1) {
    //         $registration_errors['town'] = 'Town should be greater than 1 character in length.';
    //     }

    //     if(strlen($town) > 255) {
    //         $registration_errors['town'] = 'Town should be less than 255 characters in length.';
    //     }
    // }

    // if (empty($county)) {
    //     $registration_errors['county'] = 'County is required.';
    // }

    // if (empty($occupation)) {
    //     $registration_errors['occupation'] = 'Occupation is required.';
    // } else {
    //     if(strlen($occupation) < 1) {
    //         $registration_errors['occupation'] = 'Occupation should be greater than 1 character in length.';
    //     }

    //     if(strlen($occupation) > 255) {
    //         $registration_errors['occupation'] = 'Occupation should be less than 255 characters in length.';
    //     }
    // }

    if (empty($organization_name)) {
        $registration_errors['organization_name'] = 'Organization Name is required.';
    } else {
        if(strlen($organization_name) < 1) {
            $registration_errors['organization_name'] = 'Organization Name should be greater than 1 character in length.';
        }

        if(strlen($organization_name) > 255) {
            $registration_errors['organization_name'] = 'Organization Name should be less than 255 characters in length.';
        }
    }

    if (empty($phone_number)) {
        $registration_errors['phone_number'] = 'Phone number is required.';
    } else {
        if(strlen($phone_number) < 8) {
            $registration_errors['phone_number'] = 'Phone number should be greater than 8 characters in length.';
        }

        if(strlen($phone_number) > 15) {
            $registration_errors['phone_number'] = 'Phone number should be less than 15 characters in length.';
        }
    }

    if (empty($id_number)) {
        $registration_errors['id_number'] = 'Id number is required.';
    } else {
        if(strlen($id_number) < 1) {
            $registration_errors['id_number'] = 'Id number should be greater than 1 character in length.';
        }

        if(strlen($id_number) > 55) {
            $registration_errors['id_number'] = 'Id number should be less than 55 characters in length.';
        }
    }

    if (empty($date_of_birth)) {
        $registration_errors['date_of_birth'] = 'Date of birth is required.';
    } else {
        if (!empty($date_of_birth) && !strtotime($date_of_birth)) {
            $registration_errors['date_of_birth'] = 'Invalid date of birth.';
        }
    }

    if (empty($age)) {
        $registration_errors['age'] = 'Age is required.';
    }

    if (empty($no_of_beds)) {
        $registration_errors['no_of_beds'] = 'Number of beds is required.';
    } else {
        if($no_of_beds < 1) {
            $registration_errors['no_of_beds'] = 'Number of beds should be greater than 1.';
        }

        if($no_of_beds > 5) {
            $registration_errors['no_of_beds'] = 'Number of beds should not be greater than 5.';
        }
    }

    if (empty($no_of_baths)) {
        $registration_errors['no_of_baths'] = 'Number of baths is required.';
    } else {
        if($no_of_baths < 1) {
            $registration_errors['no_of_baths'] = 'Number of baths should be greater than 1.';
        }

        if($no_of_baths > 5) {
            $registration_errors['no_of_baths'] = 'Number of baths should not be greater than 5.';
        }
    }

    if (empty($payment_plan)) {
        $registration_errors['payment_plan'] = 'Payment plan is required.';
    }

    if (empty($gender)) {
        $registration_errors['gender'] = 'Gender is required.';
    }

    if (empty($relationship_status)) {
        $registration_errors['relationship_status'] = 'Relationship status is required.';
    }

    if (empty($relationship_status)) {
        $registration_errors['promotional_emails'] = 'Promotional emails is required.';
    }

    if (empty($exclusive_emails)) {
        $registration_errors['exclusive_emails'] = 'Exclusive emails is required.';
    }

    // echo '<pre>'; // Optional: For better formatting
    // print_r($registration_errors);
    // echo '</pre>';
    
    // If no errors, process form (e.g., save to database)
    if (empty($registration_errors)) {
        require_once 'KCB_BUNI_STK_PUSH.php';
        require_once 'Response.php';
        $mpesaAPI = new MpesaAPI();
        $response = new Response();

        $registration_UUID = $tokenManager->generateUUIDv4();
        $registration_query = "INSERT INTO registrations 
            (registration_UUID, first_name, last_name, surname, id_number, kra_pin, country_id, province, city, town, occupation, date_of_birth, age, email, organization_name, no_of_beds, no_of_baths, phone_number, pymnt_pln_id, mtge_pln_id, gender_id, rlshp_sts_id, postal_code, zip_code, promotional_emails, exclusive_emails, created_at, updated_at) 
            VALUES 
            (:registration_UUID, :first_name, :last_name, :surname, :id_number, :kra_pin, :country_id, :province, :city, :town, :occupation, :date_of_birth, :age, :email, :organization_name, :no_of_beds, :no_of_baths, :phone_number, :pymnt_pln_id, :mtge_pln_id, :gender_id, :rlshp_sts_id, :postal_code, :zip_code, :promotional_emails, :exclusive_emails, :created_at, :updated_at)";

        $registration_statement = $conn->prepare($registration_query);
        
        // Bind the parameters
        $registration_statement->bindParam(':registration_UUID', $registration_UUID);
        $registration_statement->bindParam(':first_name', $first_name);
        $registration_statement->bindParam(':last_name', $last_name);
        $registration_statement->bindParam(':surname', $surname);
        $registration_statement->bindParam(':id_number', $id_number);
        $registration_statement->bindParam(':kra_pin', $kra_pin);
        $registration_statement->bindParam(':country_id', $country);
        $registration_statement->bindParam(':province', $province);
        $registration_statement->bindParam(':city', $city);
        $registration_statement->bindParam(':town', $town);
        $registration_statement->bindParam(':occupation', var: $occupation);
        $registration_statement->bindParam(':date_of_birth', $date_of_birth);
        $registration_statement->bindParam(':age', $age);
        $registration_statement->bindParam(':email', $email);
        $registration_statement->bindParam(':organization_name', $organization_name);
        $registration_statement->bindParam(':no_of_beds', $no_of_beds);
        $registration_statement->bindParam(':no_of_baths', $no_of_baths);
        $registration_statement->bindParam(':phone_number', $phone_number);
        $registration_statement->bindParam(':pymnt_pln_id', $payment_plan);
        $registration_statement->bindParam(':mtge_pln_id', $mortgage_plan);
        $registration_statement->bindParam(':gender_id', $gender);
        $registration_statement->bindParam(':rlshp_sts_id', $relationship_status);
        $registration_statement->bindParam(':postal_code', $postal_code);
        $registration_statement->bindParam(':zip_code', $zip_code);
        $registration_statement->bindParam(':promotional_emails', $promotional_emails);
        $registration_statement->bindParam(':exclusive_emails', $exclusive_emails);
        $current_timestamp = date('Y-m-d H:i:s');
        $registration_statement->bindParam(':created_at', $current_timestamp);
        $registration_statement->bindParam(':updated_at', $current_timestamp);

        // require_once 'KCB_BUNI_STK_PUSH.php';
        // require_once 'Response.php';
        // $mpesaAPI = new MpesaAPI();
        // $response = new Response();

        // $inputData = [];
        //         $inputData = [
        //             "phoneNumber" => $phone_number,
        //             "amount" => "1",
        //             "invoiceNumber" => "7869410#".$response->randomInvoiceNumber(),
        //             "sharedShortCode" => true,
        //             "orgShortCode" => "",
        //             "orgPassKey" => "",
        //             "callbackUrl" => "https://581a-197-232-61-200.ngrok-free.app/Projects/OmohHomes/omohbusiness/CallBackURL.php",
        //             "callbackUrl" => "https://omohhomes.com/jengaPamoja/CallBackURL.php",
        //             "transactionDescription" => "Registration Fees"
        //         ];
        //         $data = "inputData response0: ". json_encode($inputData);
        //         echo "<script>console.log('PHP: " . addslashes($data) . "');</script>";
        //         $mpesaAPI->handleRequest($inputData);

        // Execute the query
        try {
            // Execute the query
            $log=new Log();
            $log->wh_log('hello');

            try {
                // Log a message to the default 'logs' directory
                $log->logMessage("This is a test log message");
            
                // Log a message to a custom directory with custom permissions
                $log->logMessage("This is another test log message", "custom_logs", 0775);
            
                // Log an error message
                $log->logMessage("An error occurred: Invalid input", "error_logs");
            
                echo "Logs have been successfully written.";
            } catch (Exception $e) {
                var_dump($e->getMessage());
                echo "Error: " . $e->getMessage();
            }
    
            if ($registration_statement->execute()) {
                $data = "phone_number: " . $phone_number;
                echo "<script>console.log('PHP: " . addslashes($data) . "');</script>";
                $inputData = [];
                $inputData = [
                    "phoneNumber" => $phone_number,
                    "amount" => "1",
                    "invoiceNumber" => "7869410#".$response->randomInvoiceNumber(),
                    "sharedShortCode" => true,
                    "orgShortCode" => "",
                    "orgPassKey" => "",
                    // "callbackUrl" => "https://8796-197-232-61-237.ngrok-free.app/Projects/jengaPamoja/CallBackURL.php",
                    "callbackUrl" => "https://omohhomes.com/jengaPamoja/CallBackURL.php",
                    "transactionDescription" => "Registration Fees"
                ];

                // Log the input data to the log file
                $logMessage = "[" . date("Y-m-d H:i:s") . "] Input Data: " . json_encode($inputData, JSON_PRETTY_PRINT);
                $log->wh_log($logMessage);
                
                $mpesaAPI->handleRequest($inputData);
                // Set success message in the session
                $_SESSION['status'] = 'SUCCESS';
                $_SESSION['message'] = 'Registration was successfull!';
            } else {
                // Get more details about the error
                $errorInfo = $registration_statement->errorInfo();
                echo "Execute Error: " . $errorInfo[2]; // This will provide error message from the database

                // Log the error message
                $logMessage = "[" . date("Y-m-d H:i:s") . "] Database Error: " . $errorInfo[2];
               $log=new Log();
                $log->wh_log($logMessage);
                
                // Set error message in the session
                $_SESSION['status'] = 'ERROR';
                $_SESSION['message'] = 'Could not register.';
            }
        } catch (PDOException $e) {
            // Catch any exception that occurs during query execution
            echo "Error: " . $e->getMessage();
        
            // Set error message in the session
            $_SESSION['status'] = 'ERROR';
            // $_SESSION['message'] = 'An error occurred while processing the quotation request.';
            $_SESSION['message'] = $e->getMessage();
        }        

        // Close the connection AFTER executing the query
        $conn = null;

        // Redirect to the form page with success/error message
        // After processing, reload the page:
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();

    }

}

// Other code...
ob_end_flush(); // End output buffering
  
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quotation'])) {
    $quotation_errors = [];

    $full_names = htmlspecialchars(trim($_POST['full_names'] ?? ''), ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars(trim($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8');
    $country = htmlspecialchars($_POST['country'] ?? '', ENT_QUOTES, 'UTF-8');
    $province = htmlspecialchars(trim($_POST['province'] ?? ''), ENT_QUOTES, 'UTF-8');
    $city = htmlspecialchars(trim($_POST['city'] ?? ''), ENT_QUOTES, 'UTF-8');
    $town = htmlspecialchars(trim($_POST['town'] ?? ''), ENT_QUOTES, 'UTF-8');
    $county = htmlspecialchars(trim($_POST['county'] ?? ''), ENT_QUOTES, 'UTF-8');
    $phone_number = htmlspecialchars(trim($_POST['phone_number'] ?? ''), ENT_QUOTES, 'UTF-8');
    $id_number = htmlspecialchars(trim($_POST['id_number'] ?? ''), ENT_QUOTES, 'UTF-8');
    $date_of_birth = htmlspecialchars($_POST['date_of_birth'] ?? '', ENT_QUOTES, 'UTF-8');
    $age = (int)htmlspecialchars($_POST['age'] ?? '', ENT_QUOTES, 'UTF-8');
    $house_type = htmlspecialchars($_POST['house_type'] ?? '', ENT_QUOTES, 'UTF-8');
    $no_of_beds = (int)htmlspecialchars($_POST['no_of_beds'] ?? '', ENT_QUOTES, 'UTF-8');
    $no_of_baths = (int)htmlspecialchars($_POST['no_of_baths'] ?? '', ENT_QUOTES, 'UTF-8');

    // Validate fields
    if (empty($full_names)) {
        $quotation_errors['full_names'] = 'Full Names are required.';
    } else {
        if(strlen($full_names) < 1) {
            $quotation_errors['full_names'] = 'Full Names should be greater than 1 character in length.';
        }

        if(strlen($full_names) > 255) {
            $quotation_errors['full_names'] = 'Full Names should be less than 255 characters in length.';
        }
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $quotation_errors['email'] = 'Invalid email address.';
    } else {
        if(strlen($email) < 1) {
            $quotation_errors['email'] = 'Email should be greater than 1 character in length.';
        }

        if(strlen($full_names) > 255) {
            $quotation_errors['email'] = 'Email should be less than 255 characters in length.';
        }
    }

    if (empty($country)) {
        $quotation_errors['country'] = 'Country is required.';
    }

    // if (empty($province)) {
    //     $quotation_errors['province'] = 'Province is required.';
    // } else {
    //     if(strlen($province) < 1) {
    //         $quotation_errors['province'] = 'Province should be greater than 1 character in length.';
    //     }

    //     if(strlen($province) > 255) {
    //         $quotation_errors['province'] = 'Province should be less than 255 characters in length.';
    //     }
    // }

    if (empty($city)) {
        $quotation_errors['city'] = 'City is required.';
    } else {
        if(strlen($full_names) < 1) {
            $quotation_errors['city'] = 'City should be greater than 1 character in length.';
        }

        if(strlen($full_names) > 255) {
            $quotation_errors['city'] = 'City should be less than 255 characters in length.';
        }
    }

    // if (empty($town)) {
    //     $quotation_errors['town'] = 'Town is required.';
    // } else {
    //     if(strlen($town) < 1) {
    //         $quotation_errors['town'] = 'Town should be greater than 1 character in length.';
    //     }

    //     if(strlen($town) > 255) {
    //         $quotation_errors['town'] = 'Town should be less than 255 characters in length.';
    //     }
    // }

    // if (empty($county)) {
    //     $quotation_errors['county'] = 'County is required.';
    // }

    if (empty($phone_number)) {
        $quotation_errors['phone_number'] = 'Phone number is required.';
    } else {
        if(strlen($phone_number) < 8) {
            $quotation_errors['phone_number'] = 'Phone number should be greater than 8 characters in length.';
        }

        if(strlen($phone_number) > 15) {
            $quotation_errors['phone_number'] = 'Phone number should be less than 15 characters in length.';
        }
    }

    if (empty($id_number)) {
        $quotation_errors['id_number'] = 'Id number is required.';
    } else {
        if(strlen($id_number) < 1) {
            $quotation_errors['id_number'] = 'Id number should be greater than 1 character in length.';
        }

        if(strlen($id_number) > 55) {
            $quotation_errors['id_number'] = 'Id number should be less than 55 characters in length.';
        }
    }

    if (empty($date_of_birth)) {
        $quotation_errors['date_of_birth'] = 'Date of birth is required.';
    } else {
        if (!empty($date_of_birth) && !strtotime($date_of_birth)) {
            $quotation_errors['date_of_birth'] = 'Invalid date of birth.';
        }
    }

    if (empty($age)) {
        $quotation_errors['age'] = 'Age is required.';
    }

    if (empty($house_type)) {
        $quotation_errors['house_type'] = 'House type is required.';
    }

    if (empty($no_of_beds)) {
        $quotation_errors['no_of_beds'] = 'Number of beds is required.';
    } else {
        if($no_of_beds < 1) {
            $quotation_errors['no_of_beds'] = 'Number of beds should be greater than 1.';
        }

        if($no_of_beds > 5) {
            $quotation_errors['no_of_beds'] = 'Number of beds should not be greater than 5.';
        }
    }

    if (empty($no_of_baths)) {
        $quotation_errors['no_of_baths'] = 'Number of baths is required.';
    } else {
        if($no_of_baths < 1) {
            $quotation_errors['no_of_baths'] = 'Number of baths should be greater than 1.';
        }

        if($no_of_baths > 5) {
            $quotation_errors['no_of_baths'] = 'Number of baths should not be greater than 5.';
        }
    }

    // echo '<pre>'; // Optional: For better formatting
    // print_r($quotation_errors);
    // echo '</pre>';

    // If no errors, process form (e.g., save to database)
    if (empty($quotation_errors)) {
        $quotation_UUID = $tokenManager->generateUUIDv4();
        $quotation_query = "INSERT INTO quotations 
            (quotation_UUID, full_names, email, country_id, province, city, town, county, phone_number, id_number, date_of_birth, age, house_type_id, no_of_beds, no_of_baths, created_at, updated_at) 
            VALUES 
            (:quotation_UUID, :full_names, :email, :country_id, :province, :city, :town, :county, :phone_number, :id_number, :date_of_birth, :age, :house_type_id, :no_of_beds, :no_of_baths, :created_at, :updated_at)";

        $quotation_statement = $conn->prepare($quotation_query);

        // Bind the parameters
        $quotation_statement->bindParam(':quotation_UUID', $quotation_UUID);
        $quotation_statement->bindParam(':full_names', $full_names);
        $quotation_statement->bindParam(':email', $email);
        $quotation_statement->bindParam(':country_id', $country);
        $quotation_statement->bindParam(':province', $province);
        $quotation_statement->bindParam(':city', $city);
        $quotation_statement->bindParam(':town', $town);
        $quotation_statement->bindParam(':county', $county);
        $quotation_statement->bindParam(':phone_number', $phone_number);
        $quotation_statement->bindParam(':id_number', $id_number);
        $quotation_statement->bindParam(':date_of_birth', $date_of_birth);
        $quotation_statement->bindParam(':age', $age);
        $quotation_statement->bindParam(':house_type_id', $house_type);
        $quotation_statement->bindParam(':no_of_beds', $no_of_beds);
        $quotation_statement->bindParam(':no_of_baths', $no_of_baths);
        $current_timestamp = date('Y-m-d H:i:s');
        $quotation_statement->bindParam(':created_at', $current_timestamp);
        $quotation_statement->bindParam(':updated_at', $current_timestamp);
        
        // Execute the query
        try {
            // Execute the query
            if ($quotation_statement->execute()) {
                // Set success message in the session
                $_SESSION['status'] = 'SUCCESS';
                $_SESSION['message'] = 'Quotation requested successfully!';
            } else {
                // Get more details about the error
                $errorInfo = $quotation_statement->errorInfo();
                echo "Execute Error: " . $errorInfo[2]; // This will provide error message from the database
                
                // Set error message in the session
                $_SESSION['status'] = 'ERROR';
                $_SESSION['message'] = 'Could not request the quotation.';
            }
        } catch (PDOException $e) {
            // Catch any exception that occurs during query execution
            echo "Error: " . $e->getMessage();
        
            // Set error message in the session
            $_SESSION['status'] = 'ERROR';
            // $_SESSION['message'] = 'An error occurred while processing the quotation request.';
            $_SESSION['message'] = $e->getMessage();
        }        

        // Close the connection AFTER executing the query
        $conn = null;

        // Redirect to the form page with success/error message
        header('Location: index.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrf_token); ?>">
    <title>Omohhomes - Jenga Pamoja</title>
    <!-- Icon that shows in the browser tab when the platform is launched -->
    <link rel="shortcut icon" href="images/logos/logo.png">
    <meta name="referrer" content="no-referrer">
    <!-- Scripts -->
    <script src="js/jengaPamoja.js" defer></script>
    <!-- Script handler for github buttons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/github-buttons/2.27.0/buttons.min.js" integrity="sha512-+FbBfouZ1f3s3mNjA1PLjgJ+NNKq1+Ic8523WvBdiZ3bxpxVRVydh5+gXPZWz0SXHPQ/8gZTl99hxAfRc4g2BA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- Script handler for JQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- Script for full calendar -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <!-- Script handler for Popper Js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.8/umd/popper.min.js" integrity="sha512-TPh2Oxlg1zp+kz3nFA0C5vVC6leG/6mm1z9+mA81MI5eaUVqasPLO8Cuk4gMF4gUfP5etR73rgU/8PNMsSesoQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- Commented out these 2 bootstrap js links which were causing the issue with the nav bar collapse button not collapsing the navbar after its opened in mobile view -->
    <!-- Script handler for Bootstrap Js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js" integrity="sha512-ykZ1QQr0Jy/4ZkvKuqWn4iF3lqPZyij9iRv6sGqLRdTPkY69YX6+7wvVGmsdBbiIfN/8OdsI7HABjvEok6ZopQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- Bootstrap script that controls dropdown boxes -->
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js" integrity="sha512-7Pi/otdlbbCR+LnW+F7PwFcSDJOuUJB3OxtEHbg4vSMvzvJjde4Po1v4BR9Gdc9aXNUNFVUY+SK51wWT8WF0Gg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" defer></script> -->
    <!-- Script handler for waypoints -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/waypoints/4.0.1/noframework.waypoints.min.js" integrity="sha512-fHXRw0CXruAoINU11+hgqYvY/PcsOWzmj0QmcSOtjlJcqITbPyypc8cYpidjPurWpCnlB8VKfRwx6PIpASCUkQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- Script sources for ripple.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Ripple.js/1.2.1/ripple.js" integrity="sha512-wquKjza9uz7HBX/wy2wQVIq0VZrjKbKqsUSPeHHjEc3lOsEf1xRAoEt5+/89K1P1Ch+hhTlE+EUqKMAEtp6Usg=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Ripple.js/1.2.1/ripple.min.js" integrity="sha512-M7LdVdj6Pck0GDllHuEchDVXzPPvss3VSn3QSgBUcVLgLYq+bPCj91xKRfUSwjF/wmClLJUHEwm+p/d3OmBbtw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- Script sources for chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Font Awesome Icons JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/js/all.min.js" integrity="sha512-6sSYJqDreZRZGkJ3b+YfdhB3MzmuP9R7X1QZ6g5aIXhRvR1Y/N/P47jmnkENm7YL3oqsmI6AK+V6AD99uWDnIw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- Select2 search library for searching select drop down fields -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" integrity="sha512-2ImtlRlf2VVmiGZsjm9bEyhjGW4dU7B6TNwh/hx/iSByxNENtj3WVE6o/9Lj4TJeVXPi4bnOIMXFIJJAeufa0A=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- Google translator script -->
    <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <!-- Data Tables script -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js" integrity="sha512-BkpSL20WETFylMrcirBahHfSnY++H2O1W+UnEEO4yNIl+jI2+zowyoGJpbtk6bx97fBXf++WJHSSK2MV4ghPcg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" defer></script>

    <!-- CSS script for select2 search library for searching select drop down fields -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- CSS script source for Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" integrity="sha512-jnSuA4Ss2PkkikSOLtYs8BlYIeeIK1h99ty4YfvRPAlzr377vr3CXDb7sb7eEEBYjDtcYj+AjBH3FLv5uSJuXg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Font Awesome Icons CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Data Tables Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/jquery.dataTables.min.css" integrity="sha512-1k7mWiTNoyx2XtmI96o+hdjP8nn0f3Z2N4oF/9ZZRgijyV4omsKOXEnqL1gKQNPy2MTSP9rIEWGcH/CInulptA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Styles -->
    <link href="css/jengaPamoja.css" rel="stylesheet">
</head>

<body>
    <div class="container-fluid" id="jengaPamojaContainer">
        <div class="row">
            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">

                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 p-0">
                        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
                            <a class="navbar-brand" href="https://omohhomes.com/">
                                <img src="images/logos/logo.png" class="ms-3" id="mainNavLogo">
                            </a>
                            
                            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon"></span>
                            </button>
                                
                            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                                <ul class="navbar-nav">
                                    <li class="nav-item active">
                                        <a class="nav-link" href="https://omohhomes.com/">OMOHHOMES</a>
                                    </li>
                                    
                                    <!-- <li class="nav-item">
                                        <a class="nav-link" href="#">Features</a>
                                    </li>
                                    
                                    <li class="nav-item">
                                        <a class="nav-link" href="#">Pricing</a>
                                    </li>
                                    
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Dropdown link
                                        </a>
                                        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        </div>
                                    </li> -->
                                </ul>
                            </div>
                        </nav>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        <?php
                            // Check if a status message exists
                            if (isset($_SESSION['status']) && isset($_SESSION['message'])):
                                $alertType = $_SESSION['status'] === 'SUCCESS' ? 'alert-success' : 'alert-danger';
                            ?>

                            <div class="alert <?php echo $alertType; ?>">
                                <span><?php echo $_SESSION['message']; ?></span>

                                <button type="button" class="btn btn-close float-end" data-bs-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>

                            <?php 
                            // Clear the session message after displaying it
                            unset($_SESSION['status'], $_SESSION['message']); 
                            ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-sm-12 col-md-1 col-lg-1 col-xl-1 col-xxl-1"></div>

                    <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5 col-xxl-5">

                        <div class="row">
                            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                <h1 class="text-success display-1" id="jenga_pamoja_main_text">Jenga Pamoja</h1>
                                <h2 class="text-warning-secondary display-2" id="housing_package_main_text"><b>Housing Package</b></h2>

                                <p><b>Omoh Homes</b> provides: 
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <p>
                                                <span>&#8226;</span>
                                                <span>Unique</span>
                                            </p>
                                        </li>

                                        <li class="list-group-item">
                                            <p>
                                                <span>&#8226;</span>
                                                <span>Dynamic</span>
                                            </p>
                                        </li>

                                        <li class="list-group-item">
                                            <p>
                                                <span>&#8226;</span>
                                                <span>Affordable/Low Cost</span>
                                            </p>
                                        </li>
                                    </ul>
                                </p>

                                <p><b>Housing solutions</b> under the <span class="text-uppercase"><b>jenga pamoja housing package</b></span></p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 d-flex justify-content-center">
                                <a href="javascript:void(0);" id="registrationButton" class="btn btn-success rounded-pill text-uppercase me-3" title="Register Now">Register Now</a>

                                <a href="javascript:void(0);" id="quotationButton" class="btn btn-success rounded-pill text-uppercase me-3" title="Request Quote">Request Quote</a>

                                <a href="bronchures/OMOH-HOMES-JENGA-PAMOJA-BRONCHURE.pdf" id="bronchure" class="btn btn-success rounded-pill text-uppercase" title="Download Bronchure" download>Download Bronchure</a>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5 col-xxl-5">
                        <img src="images/housePlan.jpeg" id="housePlanImage">
                    </div>

                    <div class="col-sm-12 col-md-1 col-lg-1 col-xl-1 col-xxl-1"></div>
                </div>

                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 p-0">
                        <div id="houseDesignsWrapper">
                            <!-- Background Layer -->
                            <div id="houseDesignsOptions"></div>

                            <!-- Content Layer -->
                            <div class="row" id="houseDesignsContent">
                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                    <h2 class="text-center display-5 mt-3">
                                        <span class="text-success">House</span>
                                        <span class="text-warning-secondary">Designs</span>
                                    </h2>

                                    <div class="row pt-3">
                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4">
                                            <div class="card rounded-pill" id="houseDesignCards">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div clas="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                                            <h5 class="display-6 text-center">One Bedroom</h5>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4">
                                            <div class="card rounded-pill" id="houseDesignCards">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div clas="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                                            <h5 class="display-6 text-center">Two Bedroom</h5>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4">
                                            <div class="card rounded-pill" id="houseDesignCards">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div clas="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                                            <h5 class="display-6 text-center">Three Bedroom</h5>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        <h2 class="text-center display-5 mt-3">
                            <span class="text-success" id="ownText">Own</span> <span class="text-warning-secondary">a House</span>
                        </h2>

                        <div class="row">
                            <div class="col-sm-12 col-md-1 col-lg-1 col-xl-1 col-xxl-1"></div>
                            
                            <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5 col-xxl-5">
                                <img src="images/housePlan_2.jpg" id="housePlanImage">
                            </div>
                            
                            <div class="col-sm-12 col-md-5 col-lg-5 col-xl-5 col-xxl-5">
                                <p class="mt-5">
                                    <span>At <b>OMOH HOMES</b>, we understand that everyone’s journey to <b>homeownership</b> is <b>unique</b>. 
                                    That’s why we offer <b>3 tailored options</b> to make <b>owning your dream home</b> a <b>reality</b>. 
                                    Whether you’re ready to <b>invest upfront, prefer a gradual transition</b>, or <b>need flexible financing</b>, 
                                    we’ve got a plan that fits your needs</span>
                                    
                                    <ul class="list-group">
                                        <li class="list-group-item">
                                            <b>Buy to Own:-</b>
                                            <span>This option is perfect for those ready to make a full investment upfront. With Buy to Own, 
                                                you gain immediate ownership of your dream home by purchasing it outright. 
                                                It’s a straightforward process with no ongoing obligations beyond maintaining your property.</span>
                                        </li>

                                        <li class="list-group-item">
                                            <b>Rent to Own:-</b>
                                            <span>This plan offers flexibility for those not yet ready to buy. Start by renting the home, 
                                                with a portion of your monthly payments going toward its eventual purchase. 
                                                It’s an excellent way to work toward ownership while enjoying the benefits of living in your desired home.</span>
                                        </li>

                                        <li class="list-group-item">
                                            <b>Mortgage:-</b>
                                            <span>For those seeking financing options, our Mortgage plan provides a manageable way to own your home over time. 
                                                Work with our trusted lenders to secure a loan, make affordable monthly payments, and gradually build equity in your home.</span>
                                        </li>
                                    </ul>
                                </p>
                            </div>

                            <div class="col-sm-12 col-md-1 col-lg-1 col-xl-1 col-xxl-1"></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 p-0">
                        <div id="paymentPlanOptionsWrapper">
                            <!-- Background Layer -->
                            <div id="paymentPlanOptions"></div>

                            <!-- Content Layer -->
                            <div class="row" id="paymentPlanContent">
                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                    <h2 class="text-center display-5 mt-3">
                                        <span class="text-success">Payment</span>
                                        <span class="text-warning-secondary">Plan Options</span>
                                    </h2>

                                    <div class="row">
                                        <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3 p-3"></div>
                                        
                                        <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3 p-3">
                                            <div class="card" id="paymentPlanOptionsCards">
                                                <div class="card-body p-0">
                                                    <div class="row">
                                                        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                                            <img src="images/BTO.png" id="paymentPlanOptionsImage">
                                                        </div>
                                                    </div>

                                                    <div class="row pt-3 pb-3">
                                                        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 d-flex justify-content-center">
                                                            <a href="bronchures/OMOH-BTO-FLYER.pdf" id="bronchure" class="btn btn-success rounded-pill text-uppercase" title="Download Buy To Own Bronchure" download>Download Buy To Own Bronchure</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3 p-3">
                                            <div class="card" id="paymentPlanOptionsCards">
                                                <div class="card-body p-0">
                                                    <div class="row">
                                                        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                                            <img src="images/RTO.png" id="paymentPlanOptionsImage">
                                                        </div>
                                                    </div>

                                                    <div class="row pt-3 pb-3">
                                                        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 d-flex justify-content-center">
                                                            <a href="bronchures/OMOH-RTO-FLYER.pdf" id="bronchure" class="btn btn-success rounded-pill text-uppercase" title="Download Rent To Own Bronchure" download>Download Rent To Own Bronchure</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3 p-3"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 col-xxl-2"></div>

                    <div class="col-sm-12 col-md-8 col-lg-8 col-xl-8 col-xxl-8">
                        <div class="row">
                            <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4"></div>

                            <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4">
                                <p>
                                    Homeownership is more than just acquiring a property—it's about:
                                    <ol>
                                        <li>
                                            <span>Building a legacy</span>
                                        </li>

                                        <li>
                                            <span>Finding security</span>
                                        </li>

                                        <li>
                                            <span>Creating a space where memories flourish</span>
                                        </li>
                                    </ol>
                                </p>

                                <p>
                                    At <strong>Omoh Homes</strong>, we don’t just offer houses;
                                    <ol>
                                        <li>
                                            <span>We deliver lifestyles</span>
                                        </li>

                                        <li>
                                            <span>We deliver dreams</span>
                                        </li>

                                        <li>
                                            <span>We deliver opportunities tailored to your journey</span>
                                        </li>
                                    </ol>
                                </p>
                            </div>

                            <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4"></div>
                        </div>

                        <ul class="list-group">
                            <li class="list-group-item">
                                <p>
                                    <strong>🏡 Your Home, Your Haven:</strong> Whether it’s the cozy corner where your mornings start or the vibrant living room that hosts family celebrations, owning a home gives you the freedom to live life on your terms.
                                </p>
                            </li>

                            <li class="list-group-item">
                                <p>
                                    <strong>🔑 Unlock Endless Possibilities:</strong> With Omoh Homes, you’re not just getting walls and a roof; you’re stepping into a future of financial stability and personal empowerment. Homeownership puts the power in your hands—build equity, create wealth, and leave something behind for the next generation.
                                </p>
                            </li>

                            <li class="list-group-item">
                                <p>
                                    <strong>🌟 It’s About You:</strong> We know every homeowner's dream is unique. That’s why we’re here to listen, guide, and ensure your journey to ownership is smooth, personalized, and truly rewarding.
                                </p>
                            </li>

                            <li class="list-group-item">
                                <p>
                                    <strong>🌱 A Foundation for Growth:</strong> A home isn’t just a place; it’s a springboard for your ambitions. It provides stability and the confidence to dream bigger, knowing that you’ve secured something tangible for your future.
                                </p>
                            </li>

                            <li class="list-group-item">
                                <p>
                                    <strong>❤️ Designed with You in Mind:</strong> At Omoh Homes, we believe a home is where life’s best moments are created. It’s where you can unwind, feel safe, and build connections with your loved ones.
                                </p>
                            </li>
                        </ul>

                        <p class="text-center mt-3">
                            Make the move today. <strong>Your future deserves the stability, security, and pride that comes with homeownership.</strong> Let Omoh Homes help you build that future.
                        </p>
                    </div>

                    <div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 col-xxl-2"></div>
                </div>

                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 p-0">
                        <div id="mortgagePlanOptionsWrapper">
                            <!-- Background Layer -->
                            <div id="mortgagePlanOptions"></div>

                            <!-- Content Layer -->
                            <div class="row" id="mortgagePlanOptionsContent">
                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                    <h2 class="text-center display-5 mt-3">
                                        <span class="text-success">Mortgage</span>
                                        <span class="text-warning-secondary">Plan Options</span>
                                    </h2>

                                    <div class="row mt-3">
                                        <div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 col-xxl-2"></div>

                                        <div class="col-sm-12 col-md-8 col-lg-8 col-xl-8 col-xxl-8">
                                            <div class="row">
                                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4">
                                                    <div class="card" id="mortgagePlanOptionsCards">
                                                        <div class="card-body p-0">
                                                            <div class="row">
                                                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                                                    <img src="images/individual-investment-package.png" id="mortgagePlanOptionsImage">
                                                                </div>
                                                            </div>

                                                            <div class="row pt-3 pb-3">
                                                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 d-flex justify-content-center">
                                                                    <a href="bronchures/Individual-Investment-Package.pdf" id="bronchure" class="btn btn-success rounded-pill text-uppercase" title="Download Bronchure" download>Download Bronchure</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4">
                                                    <div class="card" id="mortgagePlanOptionsCards">
                                                        <div class="card-body p-0">
                                                            <div class="row">
                                                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                                                    <img src="images/group-investment-package.png" id="mortgagePlanOptionsImage">
                                                                </div>
                                                            </div>

                                                            <div class="row pt-3 pb-3">
                                                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 d-flex justify-content-center">
                                                                    <a href="bronchures/Group-Investment-Package.pdf" id="bronchure" class="btn btn-success rounded-pill text-uppercase" title="Download Bronchure" download>Download Bronchure</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4">
                                                    <div class="card" id="mortgagePlanOptionsCards">
                                                        <div class="card-body p-0">
                                                            <div class="row">
                                                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                                                    <img src="images/corporate-investment-package.png" id="mortgagePlanOptionsImage">
                                                                </div>
                                                            </div>

                                                            <div class="row pt-3 pb-3">
                                                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 d-flex justify-content-center">
                                                                    <a href="bronchures/Corporate-Investment-Package.pdf" id="bronchure" class="btn btn-success rounded-pill text-uppercase" title="Download Bronchure" download>Download Bronchure</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 col-xxl-2"></div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        <h2 class="text-center display-5 mt-3">
                            <span class="text-success" id="ownText">Our</span> <span class="text-warning-secondary">Partners</span>
                        </h2>

                        <div class="row mt-3">
                            <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3"></div>

                            <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3">
                                <img src="images/partners/KBA.png" id="ourPartnersImage">
                            </div>

                            <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3">
                                <img src="images/partners/KEMORA.png" id="ourPartnersImage">
                            </div>

                            <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3"></div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 p-0">
                        <div id="chooseUsWrapper">
                            <!-- Background Layer -->
                            <div id="chooseUs"></div>

                            <!-- Content Layer -->
                            <div class="row" id="chooseUsContent">
                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                    <h2 class="text-center display-5 mt-3">
                                        <span class="text-success">Why</span>
                                        <span class="text-warning-secondary">Choose Us</span>
                                    </h2>

                                    <div class="row mt-3">
                                        <div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 col-xxl-2"></div>

                                        <div class="col-sm-12 col-md-8 col-lg-8 col-xl-8 col-xxl-8">
                                            <ul class="list-group">
                                                <li class="list-group-item">
                                                    <span><strong>Tailored Solutions</strong></span>
                                                    <span>We understand that every homeowner’s journey is unique. Our plans are flexible and designed to suit your individual needs and goals.</span>
                                                </li>

                                                <li class="list-group-item">
                                                    <span><strong>Affordable Pathways</strong></span>
                                                    <span>From Rent to Own to Mortgage options, we make homeownership accessible by offering payment plans that fit your budget.</span>
                                                </li>

                                                <li class="list-group-item">
                                                    <span><strong>Transparency at Every Step</strong></span>
                                                    <span>No hidden fees, no surprises—just honest, clear communication throughout the home-buying process.</span>
                                                </li>

                                                <li class="list-group-item">
                                                    <span><strong>Quality Homes</strong></span>
                                                    <span>Our properties are built to last, combining modern design with functionality to give you a home you’ll love for years to come.</span>
                                                </li>

                                                <li class="list-group-item">
                                                    <span><strong>Trusted Partners</strong></span>
                                                    <span>We work with reliable lenders, builders, and industry professionals to ensure your journey to homeownership is smooth and stress-free.</span>
                                                </li>

                                                <li class="list-group-item">
                                                    <span><strong>Dedicated Support</strong></span>
                                                    <span>Our team is here to guide you every step of the way, providing expert advice and personalized assistance.</span>
                                                </li>

                                                <li class="list-group-item">
                                                    <span><strong>Future Investment</strong></span>
                                                    <span>Owning a home with Omoh Homes is more than a purchase—it’s an investment in stability, equity, and generational wealth.</span>
                                                </li>

                                                <li class="list-group-item">
                                                    <span><strong>Community Building</strong></span>
                                                    <span>We’re committed to creating vibrant, thriving neighborhoods where you and your family can grow and connect.</span>
                                                </li>

                                                <li class="list-group-item">
                                                    <span><strong>Your Vision, Our Priority</strong></span>
                                                    <span>We prioritize your needs and work tirelessly to deliver homes that align with your aspirations and lifestyle.</span>
                                                </li>
                                                
                                                <li class="list-group-item">
                                                    <span><strong>Unmatched Value</strong></span>
                                                    <span>With competitive pricing and a focus on quality, Omoh Homes ensures you get the best value for your investment.</span>
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 col-xxl-2"></div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">

                        <!-- Section Header -->
                        <div class="row">
                            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                <h2 class="text-center display-5 mt-3">
                                    <span class="text-success">We Are Here To Answer</span>
                                    <br>
                                    <span class="text-warning-secondary">All Your Questions</span>
                                </h2>
                            </div>
                        </div>

                        <!-- FAQ Accordion -->
                        <div class="row mt-4">
                            <div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 col-xxl-2"></div>
                            
                            <div class="col-sm-12 col-md-8 col-lg-8 col-xl-8 col-xxl-8">
                                <div class="accordion" id="faqAccordion">
                                    <!-- Question 1 -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="faqHeading1">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse1" aria-expanded="true" aria-controls="faqCollapse1">
                                                What is the process for owning a home with Omoh Homes?
                                            </button>
                                        </h2>
                                        <div id="faqCollapse1" class="accordion-collapse collapse show" aria-labelledby="faqHeading1" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body">
                                                Our process is simple and personalized. Choose a plan that suits you, work with our team to finalize details, and move into your dream home. Whether it's Buy to Own, Rent to Own, or Mortgage, we guide you every step of the way.
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Question 2 -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="faqHeading2">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse2" aria-expanded="false" aria-controls="faqCollapse2">
                                                Do you offer flexible payment options?
                                            </button>
                                        </h2>
                                        <div id="faqCollapse2" class="accordion-collapse collapse" aria-labelledby="faqHeading2" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body">
                                                Yes! We offer flexible payment plans including Rent to Own, Mortgage options, and outright Buy to Own to ensure you find a solution that fits your budget.
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Question 3 -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="faqHeading3">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse3" aria-expanded="false" aria-controls="faqCollapse3">
                                                How do I qualify for a Rent to Own plan?
                                            </button>
                                        </h2>
                                        <div id="faqCollapse3" class="accordion-collapse collapse" aria-labelledby="faqHeading3" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body">
                                                Qualifying for Rent to Own is straightforward. You’ll need proof of income, a rental agreement, and a willingness to work toward full ownership. A portion of your rent will contribute toward your future purchase.
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Question 4 -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="faqHeading4">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse4" aria-expanded="false" aria-controls="faqCollapse4">
                                                Are there any hidden fees or charges?
                                            </button>
                                        </h2>
                                        <div id="faqCollapse4" class="accordion-collapse collapse" aria-labelledby="faqHeading4" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body">
                                                No. At Omoh Homes, transparency is key. All costs and terms are clearly outlined upfront, so there are no hidden surprises during your journey to homeownership.
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Question 5 -->
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="faqHeading5">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse5" aria-expanded="false" aria-controls="faqCollapse5">
                                                Can I customize my home after purchasing it?
                                            </button>
                                        </h2>
                                        <div id="faqCollapse5" class="accordion-collapse collapse" aria-labelledby="faqHeading5" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body">
                                                Absolutely! Once you own your home, you have the freedom to customize it to your taste, turning it into a space that reflects your personality and lifestyle.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 col-xxl-2"></div>
                        </div>

                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                        <div style="width: 100%; height: 600px;">
                            <iframe 
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.8183069637935!2d36.81336027573141!3d-1.2828416356239432!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x182f10d23eb49bbb%3A0xf5ebdd7d189417a1!2sView%20Park%20Towers!5e0!3m2!1sen!2ske!4v1732051575504!5m2!1sen!2ske" 
                                width="100%" 
                                height="100%" 
                                style="border: 0;" 
                                allowfullscreen="" 
                                loading="lazy" 
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                    
                        <div class="row">
                            <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3"></div>

                            <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6">
                                <div class="card shadow rounded" id="registrationCard">
                                    <div class="card-header bg-success">
                                        <h5 class="card-title text-uppercase text-white"><b>Registration Form</b></h5>
                                    </div>

                                    <div class="card-body">
                                        <form action="index.php" method="POST" id="registrationForm" role="form" accept-charset="UTF-8">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                                            <div class="row">
                                                <!-- First Name -->
                                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                    <label for="first_name" class="form-label"><b class="text-uppercase">First Name:</b></label>
                                                    <input type="text" name="first_name" class="form-control border border-dark <?php echo isset($registration_errors['first_name']) ? 'is-invalid' : ''; ?>" id="first_name" placeholder="John" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" title="Insert your first name" minlength="1" maxlength="255" autofocus>
                                                    <?php if (isset($registration_errors['first_name'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['first_name']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Last Name -->
                                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                    <label for="last_name" class="form-label"><b class="text-uppercase">Last Name:</b></label>
                                                    <input type="text" name="last_name" class="form-control border border-dark <?php echo isset($registration_errors['last_name']) ? 'is-invalid' : ''; ?>" id="last_name" placeholder="Doe" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" title="Insert your last name" minlength="1" maxlength="255" autofocus>
                                                    <?php if (isset($registration_errors['last_name'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['last_name']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Surname -->
                                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                    <label for="surname" class="form-label"><b class="text-uppercase">Surname:</b></label>
                                                    <input type="text" name="surname" class="form-control border border-dark <?php echo isset($registration_errors['surname']) ? 'is-invalid' : ''; ?>" id="surname" placeholder="Smith" value="<?php echo htmlspecialchars($_POST['surname'] ?? ''); ?>" title="Insert your surname" minlength="1" maxlength="255" autofocus>
                                                    <?php if (isset($registration_errors['surname'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['surname']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <!-- ID Number -->
                                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                    <label for="id_number" class="form-label"><b class="text-uppercase">ID Number/Passport No/Military No:</b></label>
                                                    <input type="text" name="id_number" class="form-control border border-dark <?php echo isset($registration_errors['id_number']) ? 'is-invalid' : ''; ?>" id="id_number" placeholder="Enter ID Number" value="<?php echo htmlspecialchars($_POST['id_number'] ?? ''); ?>" minlength="1" maxlength="55" title="Enter your ID number" autofocus>
                                                    <?php if (isset($registration_errors['id_number'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['id_number']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- KRA PIN -->
                                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                    <label for="kra_pin" class="form-label"><b class="text-uppercase">KRA Pin:</b></label>
                                                    <input type="text" name="kra_pin" class="form-control border border-dark <?php echo isset($registration_errors['kra_pin']) ? 'is-invalid' : ''; ?>" id="kra_pin" placeholder="Enter KRA PIN" value="<?php echo htmlspecialchars($_POST['kra_pin'] ?? ''); ?>" minlength="1" maxlength="55" title="Enter your KRA PIN" autofocus>
                                                    <?php if (isset($registration_errors['kra_pin'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['kra_pin']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Country -->
                                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                    <label for="country" class="form-label"><b class="text-uppercase">Country:</b></label>
                                                    <select name="country" id="country" class="form-select border border-dark <?php echo isset($registration_errors['country']) ? 'is-invalid' : ''; ?>">
                                                        <option value="" disabled selected>Select Country</option>
                                                        <?php if ($countries): ?>
                                                            <?php foreach ($countries as $country): ?>
                                                                <option value="<?php echo htmlspecialchars($country['id']); ?>" <?php echo ($_POST['country'] ?? '') == $country['id'] ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars($country['country_name']); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <option value="" disabled>No countries available</option>
                                                        <?php endif; ?>
                                                    </select>
                                                    <?php if (isset($registration_errors['country'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['country']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <!-- Province -->
                                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                    <label for="province" class="form-label"><b class="text-uppercase">Province:</b></label>
                                                    <input type="text" name="province" class="form-control border border-dark <?php echo isset($registration_errors['province']) ? 'is-invalid' : ''; ?>" id="province" placeholder="Enter province" value="<?php echo htmlspecialchars($_POST['province'] ?? ''); ?>" minlength="1" maxlength="255" title="Enter your province" autofocus>
                                                    <?php if (isset($registration_errors['province'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['province']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- City -->
                                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                    <label for="city" class="form-label"><b class="text-uppercase">City:</b></label>
                                                    <input type="text" name="city" class="form-control border border-dark <?php echo isset($registration_errors['city']) ? 'is-invalid' : ''; ?>" id="city" placeholder="Enter city" value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>" minlength="1" maxlength="255" title="Enter your city" autofocus>
                                                    <?php if (isset($registration_errors['city'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['city']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Town -->
                                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                    <label for="town" class="form-label"><b class="text-uppercase">Town:</b></label>
                                                    <input type="text" name="town" class="form-control border border-dark <?php echo isset($registration_errors['town']) ? 'is-invalid' : ''; ?>" id="town" placeholder="Enter town" value="<?php echo htmlspecialchars($_POST['town'] ?? ''); ?>" minlength="1" maxlength="255" title="Enter your town" autofocus>
                                                    <?php if (isset($registration_errors['town'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['town']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <!-- Occupation -->
                                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                    <label for="occupation" class="form-label"><b class="text-uppercase">Occupation:</b></label>
                                                    <input type="text" name="occupation" class="form-control border border-dark <?php echo isset($registration_errors['occupation']) ? 'is-invalid' : ''; ?>" id="occupation" placeholder="Enter occupation" value="<?php echo htmlspecialchars($_POST['occupation'] ?? ''); ?>" minlength="1" maxlength="255" title="Enter your occupation" autofocus>
                                                    <?php if (isset($registration_errors['occupation'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['occupation']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Date of Birth -->
                                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                    <label for="date_of_birth" class="form-label"><b class="text-uppercase">Date of Birth:</b></label>
                                                    <input type="date" name="date_of_birth" class="form-control border border-dark <?php echo isset($registration_errors['date_of_birth']) ? 'is-invalid' : ''; ?>" id="date_of_birth" value="<?php echo htmlspecialchars($_POST['date_of_birth'] ?? ''); ?>" title="Enter your date of birth">
                                                    <?php if (isset($registration_errors['date_of_birth'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['date_of_birth']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Age -->
                                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                    <label for="age" class="form-label"><b class="text-uppercase">Age:</b></label>
                                                    <input type="number" name="age" class="form-control border border-dark <?php echo isset($registration_errors['age']) ? 'is-invalid' : ''; ?>" id="age" value="<?php echo htmlspecialchars($_POST['age'] ?? ''); ?>" placeholder="18" title="Enter your age">
                                                    <?php if (isset($registration_errors['age'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['age']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <!-- Email -->
                                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                    <label for="email" class="form-label"><b class="text-uppercase">Email:</b></label>
                                                    <input type="email" name="email" class="form-control border border-dark <?php echo isset($registration_errors['email']) ? 'is-invalid' : ''; ?>" id="email" placeholder="Enter email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" minlength="1" maxlength="255" title="Enter a valid email address" autofocus>
                                                    <?php if (isset($registration_errors['email'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['email']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Organization Name -->
                                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                    <label for="organization_name" class="form-label"><b class="text-uppercase">Organization Name:</b></label>
                                                    <input type="text" name="organization_name" class="form-control border border-dark <?php echo isset($registration_errors['organization_name']) ? 'is-invalid' : ''; ?>" id="organization_name" placeholder="Enter organization name" value="<?php echo htmlspecialchars($_POST['organization_name'] ?? ''); ?>" minlength="1" maxlength="255" title="Enter your organization name" autofocus>
                                                    <?php if (isset($registration_errors['organization_name'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['organization_name']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- No of Beds -->
                                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                    <label for="no_of_beds" class="form-label"><b class="text-uppercase">No. of Beds:</b></label>
                                                    <input type="number" name="no_of_beds" class="form-control border border-dark <?php echo isset($registration_errors['no_of_beds']) ? 'is-invalid' : ''; ?>" id="no_of_beds" placeholder="1" value="<?php echo htmlspecialchars($_POST['no_of_beds'] ?? ''); ?>" min="1" max="5" title="Enter no of beds">
                                                    <?php if (isset($registration_errors['no_of_beds'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['no_of_beds']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                    <!-- Number of baths -->
                                                    <label for="no_of_baths" class="form-label"><b class="text-uppercase">No. of Baths:</b></label>
                                                        <input type="number" name="no_of_baths" class="form-control border border-dark <?php echo isset($registration_errors['no_of_baths']) ? 'is-invalid' : ''; ?>" id="no_of_baths" placeholder="1" value="<?php echo htmlspecialchars($_POST['no_of_baths'] ?? ''); ?>" min="1" max="5" title="Enter no of baths">
                                                        <?php if (isset($registration_errors['no_of_baths'])): ?>
                                                            <span class="invalid-feedback alert alert-warning" role="alert">
                                                                <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['no_of_baths']; ?></strong>
                                                            </span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Phone Number -->
                                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                    <label for="phone_number" class="form-label"><b class="text-uppercase">Phone Number:</b></label>
                                                    <input type="text" name="phone_number" class="form-control border border-dark <?php echo isset($registration_errors['phone_number']) ? 'is-invalid' : ''; ?>" id="phone_number" placeholder="Enter phone number" value="<?php echo htmlspecialchars($_POST['phone_number'] ?? ''); ?>" minlength="8" maxlength="15" title="Enter your phone number" autofocus>
                                                    <?php if (isset($registration_errors['phone_number'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['phone_number']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Payment Plan -->
                                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                    <label for="payment_plan" class="form-label"><b class="text-uppercase">Payment Plan:</b></label>
                                                    <select name="payment_plan" id="payment_plan" class="form-select border border-dark <?php echo isset($registration_errors['payment_plan']) ? 'is-invalid' : ''; ?>" autofocus>
                                                        <option value="" disabled selected>Select payment plan</option>
                                                        <?php if ($payment_plans): ?>
                                                            <?php foreach ($payment_plans as $payment_plan): ?>
                                                                <option value="<?php echo htmlspecialchars($payment_plan['id']); ?>" <?php echo ($_POST['payment_plan'] ?? '') == $payment_plan['id'] ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars($payment_plan['plan_name']); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <option value="" disabled>No payment plans available</option>
                                                        <?php endif; ?>
                                                    </select>
                                                    <?php if (isset($registration_errors['payment_plan'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['payment_plan']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <!-- Mortgage Plan -->
                                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                    <label for="mortgage_plan" class="form-label"><b class="text-uppercase">Mortgage Plan:</b></label>
                                                    <select name="mortgage_plan" id="mortgage_plan" class="form-select border border-dark <?php echo isset($registration_errors['mortgage_plan']) ? 'is-invalid' : ''; ?>" autofocus>
                                                        <option value="" disabled selected>Select mortgage plan</option>
                                                        <?php if ($mortgage_plans): ?>
                                                            <?php foreach ($mortgage_plans as $mortgage_plan): ?>
                                                                <option value="<?php echo htmlspecialchars($mortgage_plan['id']); ?>" <?php echo ($_POST['mortgage_plan'] ?? '') == $mortgage_plan['id'] ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars($mortgage_plan['plan_name']); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <option value="" disabled>No mortgage plans available</option>
                                                        <?php endif; ?>
                                                    </select>
                                                    <?php if (isset($registration_errors['mortgage_plan'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['mortgage_plan']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Gender -->
                                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                    <label for="gender" class="form-label"><b class="text-uppercase">Gender:</b></label>
                                                    <select name="gender" id="gender" class="form-select border border-dark <?php echo isset($registration_errors['gender']) ? 'is-invalid' : ''; ?>" autofocus>
                                                        <option value="" disabled selected>Select gender</option>
                                                        <?php if ($genders): ?>
                                                            <?php foreach ($genders as $gender): ?>
                                                                <option value="<?php echo htmlspecialchars($gender['id']); ?>" <?php echo ($_POST['gender'] ?? '') == $gender['id'] ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars($gender['gender_identity']); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <option value="" disabled>No gender identities available</option>
                                                        <?php endif; ?>
                                                    </select>
                                                    <?php if (isset($registration_errors['gender'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['gender']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Relationship Status -->
                                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                    <label for="relationship_status" class="form-label"><b class="text-uppercase">Relationship Status:</b></label>
                                                    <select name="relationship_status" id="relationship_status" class="form-select border border-dark <?php echo isset($registration_errors['relationship_status']) ? 'is-invalid' : ''; ?>" autofocus>
                                                        <option value="" disabled selected>Select relationship status</option>
                                                        <?php if ($relationship_statuses): ?>
                                                            <?php foreach ($relationship_statuses as $relationship_status): ?>
                                                                <option value="<?php echo htmlspecialchars($relationship_status['id']); ?>" <?php echo ($_POST['relationship_status'] ?? '') == $relationship_status['relationship_status'] ? 'selected' : ''; ?>>
                                                                    <?php echo htmlspecialchars($relationship_status['relationship_status']); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <option value="" disabled>No relationship statuses available</option>
                                                        <?php endif; ?>
                                                    </select>
                                                    <?php if (isset($registration_errors['relationship_status'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['relationship_status']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <!-- Postal Code -->
                                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                    <label for="postal_code" class="form-label"><b class="text-uppercase">Postal Code:</b></label>
                                                    <input type="text" name="postal_code" class="form-control border border-dark <?php echo isset($registration_errors['postal_code']) ? 'is-invalid' : ''; ?>" id="postal_code" placeholder="Enter postal code" value="<?php echo htmlspecialchars($_POST['postal_code'] ?? ''); ?>" minlength="1" maxlength="55" title="Enter postal code" autofocus>
                                                    <?php if (isset($registration_errors['postal_code'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['postal_code']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Zip Code -->
                                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3">
                                                    <label for="zip_code" class="form-label"><b class="text-uppercase">Zip Code:</b></label>
                                                    <input type="text" name="zip_code" class="form-control border border-dark <?php echo isset($registration_errors['zip_code']) ? 'is-invalid' : ''; ?>" id="zip_code" placeholder="Enter zip code" value="<?php echo htmlspecialchars($_POST['zip_code'] ?? ''); ?>" minlength="1" maxlength="55" title="Enter zip code" autofocus>
                                                    <?php if (isset($registration_errors['zip_code'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['zip_code']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 p-3"></div>
                                            </div>

                                            <div class="row">
                                                <!-- Promotional Emails -->
                                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 p-1">
                                                    <input type="checkbox" name="promotional_emails" id="promotional_emails" class="border border-dark <?php echo isset($registration_errors['promotional_emails']) ? 'is-invalid' : ''; ?>" value="1" <?php echo isset($_POST['promotional_emails']) ? 'checked' : ''; ?> autofocus>
                                                    <label class="form-check-label" for="promotional_emails">Yes, I'd like to receive promotional emails with offers and updates.</label>
                                                    <?php if (isset($registration_errors['promotional_emails'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['promotional_emails']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <!-- Exclusive Emails -->
                                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 p-1">
                                                    <input type="checkbox" name="exclusive_emails" id="exclusive_emails" class="border border-dark <?php echo isset($registration_errors['exclusive_emails']) ? 'is-invalid' : ''; ?>" value="1" <?php echo isset($_POST['exclusive_emails']) ? 'checked' : ''; ?> autofocus>
                                                    <label class="form-check-label" for="exclusive_emails">Yes, I'd like to receive exclusive emails with discounts and product information.</label>
                                                    <?php if (isset($registration_errors['exclusive_emails'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $registration_errors['exclusive_emails']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="row mt-3">
                                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                                    <button type="submit" name="submit_registration" class="btn btn-success text-uppercase text-white float-end rounded-pill">Submit</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="card shadow rounded" id="quotationCard">
                                    <div class="card-header bg-success">
                                        <h5 class="card-title text-uppercase text-white"><b>Quotation Form</b></h5>
                                    </div>

                                    <div class="card-body">
                                        <form action="index.php" method="POST" id="quotationForm" role="form" accept-charset="UTF-8">
                                            <!-- CSRF Token -->
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                                            <div class="row">
                                                <!-- Full Names -->
                                                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6 p-3">
                                                    <label for="full_names" class="form-label"><b class="text-uppercase">Full Names:</b></label>
                                                    <input type="text" name="full_names" class="form-control border border-dark <?php echo isset($quotation_errors['full_names']) ? 'is-invalid' : ''; ?>" id="full_names" placeholder="John Doe" value="<?php echo htmlspecialchars($_POST['full_names'] ?? ''); ?>" title="Insert your full name" minlength="1" maxlength="255" autofocus>
                                                    <?php if (isset($quotation_errors['full_names'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $quotation_errors['full_names']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Email -->
                                                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6 p-3">
                                                    <label for="email" class="form-label"><b class="text-uppercase">Email:</b></label>
                                                    <input type="email" name="email" class="form-control border border-dark <?php echo isset($quotation_errors['email']) ? 'is-invalid' : ''; ?>" id="email" placeholder="example@mail.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" minlength="1" maxlength="255" title="Insert your email address" autofocus>
                                                    <?php if (isset($quotation_errors['email'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $quotation_errors['email']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <!-- Country -->
                                                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6 p-3">
                                                    <label for="country" class="form-label"><b class="text-uppercase">Country:</b></label>
                                                    <select name="country" id="country" class="form-select border border-dark <?php echo isset($quotation_errors['country']) ? 'is-invalid' : ''; ?>" autofocus>
                                                        <option value="" disabled selected>Select Country</option>
                                                        <!-- Loop through the countries and generate the options dynamically -->
                                                        <?php if ($countries): ?>
                                                            <?php foreach ($countries as $country): ?>
                                                                <option value="<?php echo htmlspecialchars($country['id']); ?>">
                                                                    <?php echo htmlspecialchars($country['country_name']); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <option value="" disabled>No countries available</option>
                                                        <?php endif; ?>
                                                    </select>
                                                    
                                                    <?php if (isset($quotation_errors['country'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $quotation_errors['country']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Province -->
                                                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6 p-3">
                                                    <label for="province" class="form-label"><b class="text-uppercase">Province:</b></label>
                                                    <input type="text" name="province" class="form-control border border-dark <?php echo isset($quotation_errors['province']) ? 'is-invalid' : ''; ?>" id="province" placeholder="Enter province" value="<?php echo htmlspecialchars($_POST['province'] ?? ''); ?>" minlength="1" maxlength="255" title="Enter your province" autofocus>
                                                    <?php if (isset($quotation_errors['province'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $quotation_errors['province']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <!-- City -->
                                                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6 p-3">
                                                    <label for="city" class="form-label"><b class="text-uppercase">City:</b></label>
                                                    <input type="text" name="city" class="form-control border border-dark <?php echo isset($quotation_errors['city']) ? 'is-invalid' : ''; ?>" id="city" placeholder="Enter city" value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>" minlength="1" maxlength="255" title="Enter your city" autofocus>
                                                    <?php if (isset($quotation_errors['city'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $quotation_errors['city']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Town -->
                                                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6 p-3">
                                                    <label for="town" class="form-label"><b class="text-uppercase">Town:</b></label>
                                                    <input type="text" name="town" class="form-control border border-dark <?php echo isset($quotation_errors['town']) ? 'is-invalid' : ''; ?>" id="town" placeholder="Enter town" value="<?php echo htmlspecialchars($_POST['town'] ?? ''); ?>" minlength="1" maxlength="255" title="Enter your town" autofocus>
                                                    <?php if (isset($quotation_errors['town'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $quotation_errors['town']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <!-- County -->
                                                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6 p-3">
                                                    <label for="county" class="form-label"><b class="text-uppercase">County:</b></label>
                                                    <input type="text" name="county" class="form-control border border-dark <?php echo isset($quotation_errors['county']) ? 'is-invalid' : ''; ?>" id="county" placeholder="Enter county" value="<?php echo htmlspecialchars($_POST['county'] ?? ''); ?>" minlength="1" maxlength="255" title="Enter your county" autofocus>
                                                    <?php if (isset($quotation_errors['county'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $quotation_errors['county']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- ID Number -->
                                                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6 p-3">
                                                    <label for="id_number" class="form-label"><b class="text-uppercase">ID Number/Passport No/Military No:</b></label>
                                                    <input type="text" name="id_number" class="form-control border border-dark <?php echo isset($quotation_errors['id_number']) ? 'is-invalid' : ''; ?>" id="id_number" placeholder="Enter ID Number" value="<?php echo htmlspecialchars($_POST['id_number'] ?? ''); ?>" minlength="1" maxlength="55" title="Enter your ID number" autofocus>
                                                    <?php if (isset($quotation_errors['id_number'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $quotation_errors['id_number']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="row">
                                                 <!-- Date of Birth -->
                                                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6 p-3">
                                                    <label for="date_of_birth" class="form-label"><b class="text-uppercase">Date of Birth:</b></label>
                                                    <input type="date" name="date_of_birth" class="form-control border border-dark <?php echo isset($quotation_errors['date_of_birth']) ? 'is-invalid' : ''; ?>" id="date_of_birth" value="<?php echo htmlspecialchars($_POST['date_of_birth'] ?? ''); ?>" title="Select your date of birth" autofocus>
                                                    <?php if (isset($quotation_errors['date_of_birth'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $quotation_errors['date_of_birth']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Age -->
                                                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6 p-3">
                                                    <label for="age" class="form-label"><b class="text-uppercase">Age:</b></label>
                                                    <input type="number" name="age" class="form-control border border-dark" id="age" placeholder="Age" value="<?php echo htmlspecialchars($_POST['age'] ?? ''); ?>" title="Insert your age" autofocus>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <!-- Phone NUmber -->
                                                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6 p-3">
                                                    <label for="phone_number" class="form-label"><b class="text-uppercase">Phone Number:</b></label>
                                                    <input type="phone" name="phone_number" class="form-control border border-dark <?php echo isset($quotation_errors['phone_number']) ? 'is-invalid' : ''; ?>" id="phone_number" placeholder="07xxxxxxxx" value="<?php echo htmlspecialchars($_POST['phone_number'] ?? ''); ?>" title="Insert your phone number" minlength="8" maxlength="15" autofocus>
                                                    <?php if (isset($quotation_errors['phone_number'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $quotation_errors['phone_number']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- House Type -->
                                                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6 p-3">
                                                    <label for="house_type" class="form-label"><b class="text-uppercase">House Type:</b></label>
                                                    <select name="house_type" id="house_type" class="form-select border border-dark <?php echo isset($quotation_errors['house_type']) ? 'is-invalid' : ''; ?>" autofocus>
                                                        <option value="" disabled selected>Select house type</option>
                                                        <!-- Loop through the countries and generate the options dynamically -->
                                                        <?php if ($house_types): ?>
                                                            <?php foreach ($house_types as $house_type): ?>
                                                                <option value="<?php echo htmlspecialchars($house_type['id']); ?>">
                                                                    <?php echo htmlspecialchars(str_replace("-", " ", $house_type['type_name'])); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <option value="" disabled>No house types available</option>
                                                        <?php endif; ?>
                                                    </select>
                                                    
                                                    <?php if (isset($quotation_errors['house_type'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $quotation_errors['house_type']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <!-- No of Beds -->
                                                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6 p-3">
                                                    <label for="no_of_beds" class="form-label"><b class="text-uppercase">No. of Beds:</b></label>
                                                    <input type="number" name="no_of_beds" class="form-control border border-dark <?php echo isset($quotation_errors['no_of_beds']) ? 'is-invalid' : ''; ?>" id="no_of_beds" placeholder="1" value="<?php echo htmlspecialchars($_POST['no_of_beds'] ?? ''); ?>" min="1" max="5" title="Enter no of beds" autofocus>
                                                    <?php if (isset($quotation_errors['no_of_beds'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $quotation_errors['no_of_beds']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- No of Baths -->
                                                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6 p-3">
                                                    <label for="no_of_baths" class="form-label"><b class="text-uppercase">No. of Baths:</b></label>
                                                    <input type="number" name="no_of_baths" class="form-control border border-dark <?php echo isset($quotation_errors['no_of_baths']) ? 'is-invalid' : ''; ?>" id="no_of_baths" placeholder="1" value="<?php echo htmlspecialchars($_POST['no_of_baths'] ?? ''); ?>" min="1" max="5" title="Enter no of baths" autofocus>
                                                    <?php if (isset($quotation_errors['no_of_baths'])): ?>
                                                        <span class="invalid-feedback alert alert-warning" role="alert">
                                                            <strong class="text-danger"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $quotation_errors['no_of_baths']; ?></strong>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- Submit -->
                                            <div class="row mt-3">
                                                <div class="col-12">
                                                    <button type="submit" name="submit_quotation" class="btn btn-success text-uppercase text-white float-end rounded-pill">Submit</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3"></div>
                        </div>

                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 p-0">
                        <footer class="footer">
                            <div class="row">
                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4">
                                    <h2>Location</h2>
                                    <p>View Park Towers, Utalii Lane P.O. Box 5941-00100</p>
                                </div>

                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4">
                                    <h2>Follow Us</h2>
                                    
                                    <div class="social-icons">
                                        <a class="text-decoration-none" href="https://www.facebook.com/omohhomeshousing" target="_blank"><i class="fab fa-facebook-f"></i>Facebook</a>

                                        <br>

                                        <a class="text-decoration-none" href="https://www.instagram.com/omoh_homes/" target="_blank"><i class="fab fa-instagram"></i>Instagram</a>
                                        
                                        <br>
                                        
                                        <a class="text-decoration-none" href="https://www........com" target="_blank"><i class="fab fa-linkedin-in"></i>Linkedin</a>
                                        
                                        <br>
                                        
                                        <a class="text-decoration-none" href="https://x.com/Omoh_Homes" target="_blank"><i class="fa-brands fa-x-twitter"></i>Twitter (X)</a>
                                        
                                        <br>

                                        <a class="text-decoration-none" href="https://www.youtube.com/@OmohHomes" target="_blank"><i class="fab fa-youtube"></i>Youtube</a>
                                    </div>
                                </div>

                                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4">
                                    <h2>Contact Us</h2>

                                    <span>Email:</span><a class="text-decoration-none ms-1" href="mailto:info@omohhomes.com">info@omohhomes.com</a>
                                    
                                    <br>
                                    
                                    <span>Phone:</span><a class="text-decoration-none ms-1" href="tel:+254 716 700 762">+254 716 700 762</a>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12">
                                    <p class="text-center">&copy; <?php echo date("Y"); ?> OmohHomes. All rights reserved.</p>
                                </div>
                            </div>
                        </footer>
                    </div>
                </div>
            
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            const $ageInput = $('#age');
            const $dobInput = $('#date_of_birth');

            // Function to calculate age based on DOB
            function calculateAge(dob) {
                const today = new Date();
                const birthDate = new Date(dob);
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                return age;
            }

            // Function to calculate date of birth based on age
            function calculateDob(age) {
                const today = new Date();
                const birthYear = today.getFullYear() - age;
                const birthDate = new Date(today.setFullYear(birthYear));
                // Ensure DOB is set to January 1st of the birth year for consistency
                birthDate.setMonth(0);
                birthDate.setDate(1);
                return birthDate.toISOString().split('T')[0]; // Return date in YYYY-MM-DD format
            }

            // Event listener for Date of Birth field
            $dobInput.on('change', function () {
                const dob = $(this).val();
                if (dob) {
                    const calculatedAge = calculateAge(dob);
                    $ageInput.val(calculatedAge);
                    $ageInput.prop('readonly', true); // Set age as readonly
                }
            });

            // Event listener for Age field
            $ageInput.on('input', function () {
                const age = $(this).val();
                if (age) {
                    const calculatedDob = calculateDob(age);
                    $dobInput.val(calculatedDob);
                    // Note: No readonly on $dobInput as per your request
                }
            });

            $("#registrationForm #phone_number").on("change", function() {
                let phoneNumber = $(this).val(); // Get the value of the input field
                
                if (phoneNumber.startsWith("0")) {
                    // If the number starts with 0, replace it with 254
                    phoneNumber = "254" + phoneNumber.substring(1);
                }
                
                // Set the updated value back to the input field
                $(this).val(phoneNumber);
            });

            $("#registrationForm #payment_plan").on("change", function() {
                if ($("#payment_plan").val() !== "3") { 
                    // Make mortgage_plan readonly and set its value to 1
                    $("#mortgage_plan").attr("readonly", true).val("1");
                } else {
                    // Remove readonly if payment_plan is 3
                    $("#mortgage_plan").removeAttr("readonly");
                }
            });
            
            // Event listener for "Register Now" button
            $('#registrationButton').click(function (e) {
                e.preventDefault(); // Prevent default link behavior
                // Show registration form and hide quotation form
                $('#registrationCard').show();
                $('#quotationCard').hide();
                // Scroll to registration form
                $('html, body').animate({
                    scrollTop: $('#registrationCard').offset().top
                }, 500); // Smooth scroll with 500ms duration
            });

            // Event listener for "Request Quote" button
            $('#quotationButton').click(function (e) {
                e.preventDefault(); // Prevent default link behavior
                // Show quotation form and hide registration form
                $('#quotationCard').show();
                $('#registrationCard').hide();
                // Scroll to quotation form
                $('html, body').animate({
                    scrollTop: $('#quotationCard').offset().top
                }, 500); // Smooth scroll with 500ms duration
            });

            // Check if there are any errors present in the form when the page loads
            if ($('#registrationCard').find('.is-invalid').length > 0) {
                // If there are errors in the registration form, show it and hide the quotation form
                $('#registrationCard').show();
                $('#quotationCard').hide();
                $('html, body').animate({
                    scrollTop: $('#registrationCard').offset().top
                }, 500); // Smooth scroll to the registration form
            } else if ($('#quotationCard').find('.is-invalid').length > 0) {
                // If there are errors in the quotation form, show it and hide the registration form
                $('#quotationCard').show();
                $('#registrationCard').hide();
                $('html, body').animate({
                    scrollTop: $('#quotationCard').offset().top
                }, 500); // Smooth scroll to the quotation form
            }
        });
    </script>
</body>

</html>
