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
        $randomPart = rand(100000, 999999);
        $timestampPart = time();
        return $randomPart . $timestampPart;
    }

    public function insertStkCallbackResponse($jsonResponse) {
        try {
            $data = json_decode($jsonResponse, true);
            error_log("Received STK callback response: " . $jsonResponse);

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
                        $balance = $item['Value'] ?? null;
                        break;
                    case 'TransactionDate':
                        $transactionDate = DateTime::createFromFormat('YmdHis', $item['Value'])->format('Y-m-d H:i:s');
                        break;
                    case 'PhoneNumber':
                        $phoneNumber = $item['Value'];
                        break;
                }
            }

            error_log("Extracted payment details - Amount: $amount, Receipt: $mpesaReceiptNumber, Phone: $phoneNumber");

            // First, insert into stk_callback_responses
            $query = "INSERT INTO stk_callback_responses (
                merchant_request_id, checkout_request_id, result_code, result_desc, 
                amount, mpesa_receipt_number, balance, transaction_date, phone_number, created_at
            ) VALUES (
                :merchantRequestID, :checkoutRequestID, :resultCode, :resultDesc,
                :amount, :mpesaReceiptNumber, :balance, :transactionDate, :phoneNumber, :createdAt
            )";

            $stmt = $this->conn->prepare($query);
            $currentTime = date('Y-m-d H:i:s');

            $stmt->bindParam(':merchantRequestID', $merchantRequestID);
            $stmt->bindParam(':checkoutRequestID', $checkoutRequestID);
            $stmt->bindParam(':resultCode', $resultCode);
            $stmt->bindParam(':resultDesc', $resultDesc);
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':mpesaReceiptNumber', $mpesaReceiptNumber);
            $stmt->bindParam(':balance', $balance);
            $stmt->bindParam(':transactionDate', $transactionDate);
            $stmt->bindParam(':phoneNumber', $phoneNumber);
            $stmt->bindValue(':createdAt', $currentTime);

            $success = $stmt->execute();
            error_log("STK callback response insert result: " . ($success ? "success" : "failed"));

            // Only proceed if payment was successful
            if ($success && $resultCode == 0 && $mpesaReceiptNumber && $phoneNumber && $amount) {
                // Begin transaction for payment insert
                $this->conn->beginTransaction();
                try {
                    // Insert into payments table
                    $paymentSuccess = $this->insertPayments($phoneNumber, $amount, $invoiceNumber);
                    error_log("Payment insert result: " . ($paymentSuccess ? "success" : "failed"));

                    if ($paymentSuccess) {
                        // Get user details for email
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
                            
                            if ($emailService->sendPaymentReceipt($userData['email'], $paymentDetails)) {
                                error_log("Email sent successfully to: " . $userData['email']);
                                $this->conn->commit();
                                return true;
                            } else {
                                error_log("Failed to send email to: " . $userData['email']);
                                $this->conn->rollBack();
                                return false;
                            }
                        } else {
                            error_log("No user data found for phone number: " . $phoneNumber);
                            $this->conn->rollBack();
                            return false;
                        }
                    } else {
                        error_log("Failed to insert payment record");
                        $this->conn->rollBack();
                        return false;
                    }
                } catch (Exception $e) {
                    error_log("Transaction error: " . $e->getMessage());
                    $this->conn->rollBack();
                    return false;
                }
            } else {
                error_log("Payment validation failed - success: $success, resultCode: $resultCode, mpesaReceiptNumber: $mpesaReceiptNumber");
                return false;
            }
        } catch (Exception $e) {
            error_log("Error in insertStkCallbackResponse: " . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    public function insertPayments($phoneNumber, $amount, $invoiceNumber, $mpesaReceiptNumber = null) {
        try {
            $query = "INSERT INTO payments (phone_number, amount, invoice_number, mpesa_receipt_number, created_at)
                    VALUES (:phone_number, :amount, :invoice_number, :mpesa_receipt_number, :created_at)";

            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(':phone_number', $phoneNumber);
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':invoice_number', $invoiceNumber);
            $stmt->bindParam(':mpesa_receipt_number', $mpesaReceiptNumber);
            $stmt->bindValue(':created_at', date('Y-m-d H:i:s'));

            $result = $stmt->execute();
            error_log("Payment insert executed with result: " . ($result ? "success" : "failed"));
            return $result;
        } catch (Exception $e) {
            error_log("Error in insertPayments: " . $e->getMessage());
            return false;
        }
    }
}