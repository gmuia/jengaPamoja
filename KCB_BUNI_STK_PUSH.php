<?php

require_once 'TokenManager.php';

class MpesaAPI
{
    private $environment;
    private $consumerKey;
    private $consumerSecret;
    private $tokenEndpoint;
    private $apiEndpoint;
    private $tokenManager;

    public function __construct($environment = 'production')
    {
        $this->environment = $environment;
        // Start of Individual developer settings
        // $this->consumerKey = $environment === 'sandbox' ? 'rtswWxYZS41jak6cQNufiN1DiAsa' : '0Q2sbXotqWYicgQi8X6RAYr9IsEa';
        // $this->consumerSecret = $environment === 'sandbox' ? 'kcAqgBsBqmHuS9MECuHRDMk81rIa' : 'wI0V7NMeDflyNIVJjzfjnfD8qw0a';
        // $this->tokenEndpoint = 'https://wso2-api-gateway-direct-kcb-wso2-gateway.apps.test.aro.kcbgroup.com/token';
        // $this->apiEndpoint = 'https://uat.buni.kcbgroup.com/mm/api/request/1.0.0/stkpush';
        // End of individual developer settings

        // Start of organization account settings
        $this->consumerKey = $environment === 'sandbox' ? 'fNL39U5Qtq2tYZG7MF4WCoPk0Aka' : 'l5FtgXzcwZ99YY9tvVCKITOsL9wa';
        $this->consumerSecret = $environment === 'sandbox' ? '25t4yZjlQPQvvvWu9RQKbISN4jIa' : 'BLwtb06ftjocr8241dcdKlNcerca';
        $this->tokenEndpoint = 'https://api.buni.kcbgroup.com/token?grant_type=client_credentials';
        $this->apiEndpoint = 'https://api.buni.kcbgroup.com/mm/api/request/1.0.0/stkpush';
        // End of organization account settings

        $this->tokenManager = new TokenManager();
    }

    public function handleRequest($inputData)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return $this->getToken();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->sendStkPushRequest($inputData);
        } else {
            http_response_code(405);
            return json_encode(['error' => 'Invalid request method. Only GET and POST are allowed.']);
        }
    }

    private function getToken()
    {
        $accessToken = $this->tokenManager->getValidAccessToken($this->consumerKey, $this->consumerSecret, $this->tokenEndpoint);
        if ($accessToken) {
            return json_encode(['success' => 'Token retrieved successfully.', 'token' => $accessToken]);
        } else {
            http_response_code(500);
            return json_encode(['error' => 'Unable to retrieve access token.']);
        }
    }

    private function sendStkPushRequest($inputData)
    {
        $accessToken = $this->tokenManager->getValidAccessToken($this->consumerKey, $this->consumerSecret, $this->tokenEndpoint);
        if (!$accessToken) {
            http_response_code(500);
            return json_encode(['error' => 'Unable to retrieve access token.']);
        }

        // $inputData = json_decode(file_get_contents('php://input'), true);
        // if (!isset($inputData['phoneNumber'], $inputData['amount'], $inputData['invoiceNumber'], $inputData['callbackUrl'])) {
        //     http_response_code(400);
        //     return json_encode(['error' => 'Missing required fields: phoneNumber, amount, invoiceNumber, or callbackUrl.']);
        // }
        
        $data = [
            "phoneNumber" => $inputData['phoneNumber'],
            "amount" => $inputData['amount'],
            "invoiceNumber" => $inputData['invoiceNumber'],
            "sharedShortCode" => $inputData['sharedShortCode'],
            "orgShortCode" => $inputData['orgShortCode'] ?? "",
            "orgPassKey" => $inputData['orgPassKey'] ?? "",
            "callbackUrl" => $inputData['callbackUrl'],
            "transactionDescription" => $inputData['transactionDescription']
        ];

        $options = [
            CURLOPT_URL => $this->apiEndpoint,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ]
        ];


        $ch = curl_init();
        curl_setopt_array($ch, $options);
       
        $response = curl_exec($ch);

        $data1 = "Response: " . json_encode($response);
        echo "<script>console.log('PHP: " . addslashes($data1) . "');</script>";

        if (curl_errno($ch)) {
            http_response_code(500);
            $errorResponse = json_encode(['error' => 'CURL ERROR: ' . curl_error($ch)]);
        } else {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $errorResponse = json_encode([
                'status_code' => $httpCode,
                'response' => json_decode($response, true)
            ]);
        }

        curl_close($ch);

        return $errorResponse;
    }
}

// Usage
$mpesaAPI = new MpesaAPI();
// $invoiceNumber = '13272722';
// $invoiceNumber = new Response();

// $inputData = [
//     "phoneNumber" => "254712918797",
//     "amount" => "1",
//     "invoiceNumber" => "7869410#".$invoiceNumber->randomInvoiceNumber(),
//     "sharedShortCode" => true,
//     "orgShortCode" => "",
//     "orgPassKey" => "",
//     "callbackUrl" => "https://581a-197-232-61-200.ngrok-free.app/Projects/jengaPamoja/CallBackURL.php",
//     "transactionDescription" => "Test payment"
// ];

// echo $mpesaAPI->handleRequest($inputData);

?>
