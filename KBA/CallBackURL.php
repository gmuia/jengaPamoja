<?php

header("Content-Type: application/json");

// Read the callback response from input
$stk_call_back_response = file_get_contents("php://input");

// Log the response to a file
$log_file = "Mpesa_STK_response.json";
$log = fopen($log_file, "a");

if (!$log) {
    // Error if file cannot be opened
    http_response_code(500);
    die(json_encode(['error' => 'Could not open the log file for writing.']));
}

if (fwrite($log, $stk_call_back_response) === false) {
    // Error if write operation fails
    http_response_code(500);
    fclose($log);
    die(json_encode(['error' => 'Could not write to the log file.']));
}

fclose($log);

// Split the concatenated JSON into individual objects
$jsonObjects = preg_split('/(?<=})\s*(?={)/', $stk_call_back_response);

foreach ($jsonObjects as $jsonObject) {
    // Trim whitespace and decode each JSON object individually
    $jsonObject = trim($jsonObject);

    $data = json_decode($jsonObject);

    if (json_last_error() !== JSON_ERROR_NONE) {
        // Output error for debugging if JSON decoding fails
        echo "JSON decode error: " . json_last_error_msg() . "\n";
        continue; // Skip to next object if current one is invalid
    }

    // Extract the necessary fields from the decoded data
    // $merchant_request_ID = $data->Body->stkCallback->MerchantRequestID ?? null;
    // $check_out_request_ID = $data->Body->stkCallback->CheckoutRequestID ?? null;
    $result_code = $data->Body->stkCallback->ResultCode ?? null;
    $result_desc = $data->Body->stkCallback->ResultDesc ?? null;
    // $amount = $data->Body->stkCallback->CallbackMetadata->Item[0]->Value ?? null;
    // $transaction_ID = $data->Body->stkCallback->CallbackMetadata->Item[1]->Value ?? null;
    // $user_phone_number = $data->Body->stkCallback->CallbackMetadata->Item[4]->Value ?? null;

    // // Check if all required fields are present
    // if (!$merchant_request_ID || !$check_out_request_ID || $result_code === null || !$result_desc || !$amount || !$transaction_ID || !$user_phone_number) {
    //     http_response_code(400);
    //     die(json_encode(['error' => 'Missing required fields in the callback response.']));
    // }

    // Process transaction data
    switch ($result_code) {
        case 0:
            // Transaction was successful
            require_once 'Response.php';
            $response = new Response();
            $stored_response = $response->insertStkCallbackResponse($jsonObject);

            // Add your database insertion code here
            echo json_encode(['success' => 'Transaction was successful.']);
            break;

        case 1032:
            // Transaction was successful
            require_once 'Response.php';
            $response = new Response();
            $stored_response = $response->insertCancelledStkCallbackResponse($jsonObject);

            // Add your database insertion code here
            echo json_encode(['success' => 'Transaction was cancelled.']);
            break;

        default:
            // Transaction failed or encountered an error
            echo json_encode([
                'error' => 'Transaction failed.',
                'result_code' => $result_code,
                'result_desc' => $result_desc
            ]);
            break;
    }
}

?>
