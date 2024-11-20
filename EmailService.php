<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once 'Database.php';
require '../vendor/autoload.php';

class EmailService {
    private $mailer;
    private $conn;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $database = new Database();
        $this->conn = $database->connect();
        
        // Configure SMTP settings
        $this->mailer->isSMTP();
        $this->mailer->Host = 'smtp.hostinger.com';  // Update this with your SMTP server
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = 'no-reply@omohhomes.com'; // Update with your email
        $this->mailer->Password = 'OM#OHHOm18S+TAFF';    // Update with your password
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = 587;
        
        // Enable debug output for testing
        $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
    }

    public function sendPaymentReceipt($email, $paymentDetails) {
        try {
            // Get user details from database
            $stmt = $this->conn->prepare("SELECT first_name, last_name FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Set email parameters
            $this->mailer->setFrom('no-reply@omohhomes.com', 'Omoh Homes'); // Update with your email
            $this->mailer->addAddress($email, $user['first_name'] . ' ' . $user['last_name']);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Registration Payment Receipt - Omoh Homes';

            // Generate receipt HTML
            $receiptHtml = $this->generateReceiptHtml($paymentDetails, $user);
            $this->mailer->Body = $receiptHtml;
            $this->mailer->AltBody = $this->generatePlainTextReceipt($paymentDetails, $user);

            // Send email
            $this->mailer->send();
            error_log("Email sent successfully to: " . $email);
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }

    private function generateReceiptHtml($paymentDetails, $user) {
        $date = date('F j, Y');
        $receiptNumber = $paymentDetails['mpesaReceiptNumber'];
        
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .receipt { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 20px; }
                .details { margin: 20px 0; }
                .details p { margin: 10px 0; }
                .amount { font-size: 24px; font-weight: bold; text-align: center; margin: 30px 0; color: #28a745; }
                .footer { margin-top: 30px; font-size: 12px; color: #666; text-align: center; border-top: 1px solid #eee; padding-top: 20px; }
            </style>
        </head>
        <body>
            <div class="receipt">
                <div class="header">
                    <h1 style="color: #28a745;">Payment Receipt</h1>
                    <p>Omoh Homes</p>
                </div>
                
                <div class="details">
                    <p><strong>Date:</strong> {$date}</p>
                    <p><strong>Receipt Number:</strong> {$receiptNumber}</p>
                    <p><strong>Customer:</strong> {$user['first_name']} {$user['last_name']}</p>
                    <p><strong>Phone Number:</strong> {$paymentDetails['phoneNumber']}</p>
                    <p><strong>Transaction Date:</strong> {$paymentDetails['transactionDate']}</p>
                </div>
                
                <div class="amount">
                    Amount Paid: KES {$paymentDetails['amount']}
                </div>
                
                <div class="footer">
                    <p>Thank you for choosing Omoh Homes.</p>
                    <p>For any queries, please contact our support team.</p>
                    <p>This is an automated receipt. Please keep it for your records.</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }

    private function generatePlainTextReceipt($paymentDetails, $user) {
        $date = date('F j, Y');
        return "
            Payment Receipt - Omoh Homes
            
            Date: {$date}
            Receipt Number: {$paymentDetails['mpesaReceiptNumber']}
            Customer: {$user['first_name']} {$user['last_name']}
            Phone Number: {$paymentDetails['phoneNumber']}
            Transaction Date: {$paymentDetails['transactionDate']}
            
            Amount Paid: KES {$paymentDetails['amount']}
            
            Thank you for choosing Omoh Homes.
            For any queries, please contact our support team.
        ";
    }
}