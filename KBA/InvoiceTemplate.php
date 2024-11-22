<?php

require_once 'vendor/setasign/fpdf/fpdf.php';

class InvoiceTemplate extends FPDF {
    private $data;

    public function __construct($data) {
        parent::__construct();
        $this->data = $data;
    }

    public function Header() {
        // Add logo - reduced size from 50 to 35 and adjusted position
        $this->Image('assets/images/omoh-logo.png', 160, 8, 25);
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, '', 0, 1, 'C');
        $this->Ln(10); // Reduced from 20 to 15 to reduce space after logo
    }

    public function generateInvoice() {
        $this->AddPage();
        
        // Header Section
        $this->SetFillColor(217, 242, 208); // Light green background
        $this->SetTextColor(58, 125, 34); // Green text color
        
        // Payment Receipt Title
        $this->SetFont('Arial', 'B', 20);
        $this->Cell(100, 10, 'PAYMENT RECEIPT', 0, 0, 'L');
        
        // Right side information
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(45, 10, 'Invoice Date:', 0, 0, 'R');
        $this->SetFont('Arial', '', 10);
        $this->Cell(45, 10, $this->data['payment_date'], 0, 1, 'R');
        
        // Customer Information
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(100, 8, 'CUSTOMER NAME: ' . $this->data['customer_name'], 0, 1, 'L');
        $this->Cell(100, 8, 'CUSTOMER PIN: ' . $this->data['kra_pin'], 0, 1, 'L');
        $this->Cell(100, 8, 'Customer ID: ' . $this->data['id_number'], 0, 1, 'L');
        
        // Invoice Details
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(100, 8, 'Invoice Number: ' . $this->data['invoice_number'], 0, 1, 'L');
        $this->Cell(100, 8, 'Mpesa Transaction Code: ' . $this->data['transaction_id'], 0, 1, 'L');
        
        $this->Ln(10);
        
        // Table Header
        $this->SetFillColor(217, 242, 208);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(20, 10, 'Item', 1, 0, 'C', true);
        $this->Cell(90, 10, 'Description', 1, 0, 'C', true);
        $this->Cell(30, 10, 'Quantity', 1, 0, 'C', true);
        $this->Cell(25, 10, 'Unit Price', 1, 0, 'C', true);
        $this->Cell(25, 10, 'Amount KES', 1, 1, 'C', true);
        
        // Item Details
        $this->SetFont('Arial', '', 10);
        $this->Cell(20, 10, '001', 1, 0, 'C');
        
        // Description cell with multiple lines
        $this->SetFont('Arial', 'B', 10);
        $description = "Project Name: Jenga Pamoja Housing Package\n";
        $description .= "Unit Size: " . $this->data['beds'] . " Bedroom\n";
        $description .= "Bath Size: " . $this->data['baths'] . " Bathroom\n";
        $description .= "Payment Plan: " . $this->data['payment_plan'];
        
        $this->MultiCell(90, 10, $description, 1, 'L');
        $currentY = $this->GetY();
        
        // Complete the row
        $this->SetY($currentY - 40); // Adjust based on description height
        $this->Cell(110); // Move past item and description
        $this->Cell(30, 40, '1', 1, 0, 'C');
        $this->Cell(25, 40, number_format($this->data['amount'], 2), 1, 0, 'R');
        $this->Cell(25, 40, number_format($this->data['amount'], 2), 1, 1, 'R');
        
        // Total
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(150, 10, 'TOTAL KES', 1, 0, 'R');
        $this->Cell(40, 10, number_format($this->data['amount'], 2), 1, 1, 'R');
        
        // Footer information
        $this->Ln(10);
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 5, 'OMOH Homes Ltd', 0, 1, 'L');
        $this->Cell(0, 5, 'Contact: +254 716 700 762', 0, 1, 'L');
        $this->Cell(0, 5, 'Email: sales@omohhomes.com | jenga@omohhomes.com', 0, 1, 'L');
        $this->Cell(0, 5, 'Website: www.omohhomes.com', 0, 1, 'L');
        $this->Cell(0, 5, 'Address: Funzi RD (Off) Enterprise Rd, Nairobi. Building 04, 1st Floor', 0, 1, 'L');
    }
}