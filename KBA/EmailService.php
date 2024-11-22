<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once 'InvoiceTemplate.php';
require 'vendor/autoload.php';

class EmailService {
    private $mailer;

    public function __construct() {
        try {
            // Initialize mailer
            $this->mailer = new PHPMailer(true);
            
            // Configure SMTP settings
            $this->mailer->isSMTP();
            $this->mailer->Host = 'smtp.hostinger.com';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = 'no-reply@omohhomes.com';
            $this->mailer->Password = 'OM#OHHOm18S+TAFF';
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = 587;
            
            // Disable debug output for production
            $this->mailer->SMTPDebug = 0;
        } catch (Exception $e) {
            error_log("EmailService initialization failed: " . $e->getMessage());
            throw $e;
        }
    }

    public function sendPaymentReceipt($email, $paymentDetails) {
        try {
            // Prepare data for invoice
            $invoiceData = [
                'customer_name' => $paymentDetails['first_name'] . ' ' . $paymentDetails['last_name'],
                'kra_pin' => $paymentDetails['kra_pin'],
                'id_number' => $paymentDetails['id_number'],
                'payment_date' => date('F j, Y'),
                'invoice_number' => $paymentDetails['invoice_number'],
                'transaction_id' => $paymentDetails['mpesaReceiptNumber'],
                'beds' => $paymentDetails['beds'],
                'baths' => $paymentDetails['baths'],
                'payment_plan' => $paymentDetails['paymentPlan'],
                'amount' => $paymentDetails['amount']
            ];

            // Generate PDF
            $pdf = new InvoiceTemplate($invoiceData);
            $pdf->generateInvoice();
            $pdfPath = sys_get_temp_dir() . '/Invoice_' . $paymentDetails['invoice_number'] . '.pdf';
            $pdf->Output('F', $pdfPath);

            // Set email parameters
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            $this->mailer->setFrom('no-reply@omohhomes.com', 'Omoh Homes');
            $this->mailer->addAddress($email, $invoiceData['customer_name']);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Payment Confirmation and Invoice for Jenga Pamoja Housing Package';

            // Email body
            $emailBody = $this->generateEmailBody($invoiceData['customer_name']);
            $this->mailer->Body = $emailBody;
            $this->mailer->AltBody = strip_tags($emailBody);

            // Attach PDF
            $this->mailer->addAttachment($pdfPath, 'Invoice_' . $paymentDetails['invoice_number'] . '.pdf');

            // Send email
            $result = $this->mailer->send();
            
            // Clean up
            if (file_exists($pdfPath)) {
                unlink($pdfPath);
            }

            error_log("Email sent successfully to: " . $email);
            return true;

        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            // Clean up temporary PDF file in case of error
            if (isset($pdfPath) && file_exists($pdfPath)) {
                unlink($pdfPath);
            }
            return false;
        }
    }

    private function generateEmailBody($customerName) {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .content { margin-bottom: 30px; }
                .footer { font-size: 12px; color: #666; text-align: center; margin-top: 30px; }
                .highlight { color: #28a745; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Payment Confirmation</h2>
                </div>
                
                <div class="content">
                    <p>Dear {$customerName},</p>
                    
                    <p>Thank you for registering with Omoh Homes under the Jenga Pamoja Housing Package. We are excited to have you join our community of homeowners!</p>
                    
                    <p>We are pleased to confirm that we have received your payment successfully. Please find attached your official payment receipt in PDF format. This document contains all the details of your transaction for your records.</p>
                    
                    <p>Important Next Steps:</p>
                    <ul>
                        <li>Please save the attached receipt for your records</li>
                        <li>Review all the payment details carefully</li>
                        <li>Contact us immediately if you notice any discrepancies</li>
                    </ul>
                    
                    <p>If you have any questions or require assistance, our support team is always ready to help:</p>
                    <ul>
                        <li>Email: <span class="highlight">jenga@omohhomes.com</span></li>
                        <li>Phone: <span class="highlight">+254 716 700 762</span></li>
                        <li>WhatsApp: <span class="highlight">+254 716 700 762</span></li>
                    </ul>
                </div>
                
                <div class="footer">
                    <p>Thank you for choosing Omoh Homes. We look forward to helping you achieve your dream of homeownership.</p>
                    <p>Best regards,<br>The Omoh Homes Team</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }
}