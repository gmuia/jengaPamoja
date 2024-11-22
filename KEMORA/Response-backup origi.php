<?php

require_once 'Database.php';
require_once 'EmailService.php';

class Response {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function randomInvoiceNumber() {
        // Generate a random number, for example, between 100000 and 999999
        $randomPart = rand(100000, 999999);
        // Get the current timestamp (seconds since Unix epoch)
        $timestampPart = time();
        // Combine the random part and timestamp to form a unique invoice number
        // You could format it further if needed, e.g., padding, adding dashes, etc.
        return $randomPart . $timestampPart;
    }

    public function insertStkCallbackResponse($jsonResponse) {
        $data = json_decode($jsonResponse, true);

        // Parse response data
        $merchantRequestID = $data['Body']['stkCallback']['MerchantRequestID'] ?? null;
        $checkoutRequestID = $data['Body']['stkCallback']['CheckoutRequestID'] ?? null;
        $resultCode = $data['Body']['stkCallback']['ResultCode'] ?? null;
        $resultDesc = $data['Body']['stkCallback']['ResultDesc'] ?? null;

        // Extract CallbackMetadata if it exists
        $callbackMetadata = $data['Body']['stkCallback']['CallbackMetadata']['Item'] ?? [];
        $amount = null;
        $mpesaReceiptNumber = null;
        $balance = null;
        $transactionDate = null;
        $phoneNumber = null;
        $invoiceNumber = $this->randomInvoiceNumber();

        // Loop through metadata to find values
        foreach ($callbackMetadata as $item) {
            switch ($item['Name']) {
                case 'Amount':
                    $amount = $item['Value'];
                    break;
                case 'MpesaReceiptNumber':
                    $mpesaReceiptNumber = $item['Value'];
                    break;
                case 'Balance':
                    $balance = $item['Value'] ?? null; // Optional balance
                    break;
                case 'TransactionDate':
                    $transactionDate = DateTime::createFromFormat('YmdHis', $item['Value'])->format('Y-m-d H:i:s');
                    break;
                case 'PhoneNumber':
                    $phoneNumber = $item['Value'];
                    break;
            }
        }

        $this->insertPayments($phoneNumber, $amount, $invoiceNumber);

        // SQL Insert statement
        $query = "INSERT INTO stk_callback_responses (merchant_request_id, checkout_request_id, result_code, result_desc, amount, mpesa_receipt_number, balance,
                    transaction_date, phone_number, created_at) VALUES (:merchantRequestID, :checkoutRequestID, :resultCode, :resultDesc, :amount, :mpesaReceiptNumber,
                    :balance, :transactionDate, :phoneNumber, :createdAt)";

        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(':merchantRequestID', $merchantRequestID);
        $stmt->bindParam(':checkoutRequestID', $checkoutRequestID);
        $stmt->bindParam(':resultCode', $resultCode);
        $stmt->bindParam(':resultDesc', $resultDesc);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':mpesaReceiptNumber', $mpesaReceiptNumber);
        $stmt->bindParam(':balance', $balance);
        $stmt->bindParam(':transactionDate', $transactionDate);
        $stmt->bindParam(':phoneNumber', $phoneNumber);
        $stmt->bindValue(':createdAt', date('Y-m-d H:i:s'));

        // Execute the insert and return success or failure
        // return $stmt->execute();

        
        // Begin of input for Email handling. If any errors arrise, comment out this block and uncomment the last return
        // Execute the insert
        $success = $stmt->execute();

        // If payment was successful (ResultCode = 0) and we have the M-PESA receipt number
        if ($success && $resultCode == 0 && $mpesaReceiptNumber) {
            try {
                // Get user details from the database using phone number with plan names
                $userQuery = "SELECT 
                            r.first_name, 
                            r.surname, 
                            r.email, 
                            r.kra_pin, 
                            r.id_number,
                            COALESCE(pp.plan_name, 'N/A') as payment_plan,
                            COALESCE(mp.plan_name, 'N/A') as mortgage_plan
                            FROM registrations r
                            LEFT JOIN payment_plans pp ON r.pymnt_pln_id = pp.id
                            LEFT JOIN mortgage_plans mp ON r.mtge_pln_id = mp.id
                            WHERE r.phone_number = :phoneNumber 
                            ORDER BY r.created_at DESC LIMIT 1";
                error_log("Executing query: " . $userQuery . " with phone number: " . $phoneNumber);
                
                $userStmt = $this->conn->prepare($userQuery);
                $userStmt->bindParam(':phoneNumber', $phoneNumber);
                $userStmt->execute();
                $userData = $userStmt->fetch(PDO::FETCH_ASSOC);

                if ($userData) {
                    error_log("User data found: " . json_encode($userData));
                    $emailService = new EmailService();
                    $paymentDetails = [
                        'first_name' => $userData['first_name'],
                        'last_name' => $userData['surname'],
                        'kra_pin' => $userData['kra_pin'],
                        'id_number' => $userData['id_number'],
                        'invoice_number' => $invoiceNumber,
                        'mpesaReceiptNumber' => $mpesaReceiptNumber,
                        'paymentPlan' => $userData['payment_plan'],
                        'mortgagePlan' => $userData['mortgage_plan'],
                        'amount' => $amount,
                        'transactionDate' => $transactionDate
                    ];
                    error_log("Payment details prepared: " . json_encode($paymentDetails));
                    
                    if ($emailService->sendPaymentReceipt($userData['email'], $paymentDetails)) {
                        error_log("Email sent successfully to: " . $userData['email']);
                    } else {
                        error_log("Failed to send email to: " . $userData['email']);
                    }
                } else {
                    error_log("No user data found for phone number: " . $phoneNumber);
                }
            } catch (Exception $e) {
                error_log("Error in Response.php: " . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
            }
        } else {
            error_log("Payment validation failed - success: $success, resultCode: $resultCode, mpesaReceiptNumber: $mpesaReceiptNumber");
        }

        return $success;

        // End of input for Email handling.

    }

    public function insertCancelledStkCallbackResponse($jsonResponse) {
        $data = json_decode($jsonResponse, true);

        // Parse the necessary response data for canceled responses
        $merchantRequestID = $data['Body']['stkCallback']['MerchantRequestID'] ?? null;
        $checkoutRequestID = $data['Body']['stkCallback']['CheckoutRequestID'] ?? null;
        $resultCode = $data['Body']['stkCallback']['ResultCode'] ?? null;
        $resultDesc = $data['Body']['stkCallback']['ResultDesc'] ?? null;

        // Ensure all required fields are present
        if (is_null($merchantRequestID) || is_null($checkoutRequestID) || is_null($resultCode) || is_null($resultDesc)) {
            // Handle missing required fields, possibly log or return an error
            return false;
        }

        // SQL Insert statement for canceled responses
        $query = "INSERT INTO stk_callback_cancelled_responses (merchant_request_id, checkout_request_id, result_code, result_desc, canceled_at, created_at)
                  VALUES (:merchantRequestID, :checkoutRequestID, :resultCode, :resultDesc, :canceledAt, :createdAt)";

        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(':merchantRequestID', $merchantRequestID);
        $stmt->bindParam(':checkoutRequestID', $checkoutRequestID);
        $stmt->bindParam(':resultCode', $resultCode);
        $stmt->bindParam(':resultDesc', $resultDesc);
        $stmt->bindValue(':canceledAt', date('Y-m-d H:i:s')); // Set cancellation timestamp
        $stmt->bindValue(':createdAt', date('Y-m-d H:i:s'));

        // Execute the insert and return success or failure
        return $stmt->execute();
    }

    public function insertPayments($phoneNumber, $amount, $invoiceNumber) {
        $query = "INSERT INTO payments (phone_number, amount, invoice_number, created_at)
                  VALUES (:phone_number, :amount, :invoice_number, :created_at)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':phone_number', $phoneNumber);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':invoice_number', $invoiceNumber);
        $stmt->bindValue(':created_at', date('Y-m-d H:i:s'));

        return $stmt->execute();
    }

}

$invoiceNumber = new Response();

?>



