<?php

require_once 'Database.php';

class TokenManager {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function generateUUIDv4() {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function insertToken($tokenName, $tokenValue, $expiryTime, $tokenType, $expiresIn) {
        $tokenUUID = $this->generateUUIDv4();

        $query = "INSERT INTO tokens (token_UUID, token_name, token_value, expiry_time, token_type, expires_in, is_token_expired, created_at)
                  VALUES (:token_UUID, :token_name, :token_value, :expiry_time, :token_type, :expires_in, :is_token_expired, :created_at)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':token_UUID', $tokenUUID);
        $stmt->bindParam(':token_name', $tokenName);
        $stmt->bindParam(':token_value', $tokenValue);
        $stmt->bindParam(':expiry_time', $expiryTime);
        $stmt->bindParam(':token_type', $tokenType);
        $stmt->bindParam(':expires_in', $expiresIn);
        $stmt->bindValue(':is_token_expired', false, PDO::PARAM_BOOL);
        $stmt->bindValue(':created_at', date('Y-m-d H:i:s'));

        return $stmt->execute();
    }

    public function getValidAccessToken($CONSUMER_KEY, $CONSUMER_SECRET, $TOKEN_ENDPOINT) {
        $query = "SELECT * FROM tokens WHERE is_token_expired = 0 ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $existingToken = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingToken) {
            $createdAt = new DateTime($existingToken['created_at']);
            $expiresIn = $existingToken['expires_in'];
            $finalTime = clone $createdAt;
            $finalTime->add(new DateInterval('PT' . $expiresIn . 'S'));

            if (new DateTime() > $finalTime) {
                $updateQuery = "UPDATE tokens SET is_token_expired = 1 WHERE id = :id";
                $updateStmt = $this->conn->prepare($updateQuery);
                $updateStmt->bindParam(':id', $existingToken['id']);
                $updateStmt->execute();

                $newTokenData = $this->requestNewToken($CONSUMER_KEY, $CONSUMER_SECRET, $TOKEN_ENDPOINT);
                return $newTokenData['tokenData']['access_token'] ?? null;
            } else {
                return $existingToken['token_value'];
            }
        } else {
            $newTokenData = $this->requestNewToken($CONSUMER_KEY, $CONSUMER_SECRET, $TOKEN_ENDPOINT);
            return $newTokenData['tokenData']['access_token'] ?? null;
        }
    }

    private function requestNewToken($CONSUMER_KEY, $CONSUMER_SECRET, $TOKEN_ENDPOINT) {
        $data = ['grant_type' => 'client_credentials'];
        $postData = http_build_query($data);
        $options = [
            CURLOPT_URL => $TOKEN_ENDPOINT,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Authorization: Basic ' . base64_encode($CONSUMER_KEY . ':' . $CONSUMER_SECRET)
            ]
        ];
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            return ['error' => 'CURL ERROR: ' . curl_error($ch)];
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $tokenData = json_decode($response, true);
            if (isset($tokenData['access_token'], $tokenData['token_type'], $tokenData['expires_in'])) {
                $expiryTime = date('Y-m-d H:i:s', time() + $tokenData['expires_in']);
                $this->insertToken('KCB Access Token', $tokenData['access_token'], $expiryTime, $tokenData['token_type'], $tokenData['expires_in']);
                return ['success' => 'New token generated and stored successfully.', 'tokenData' => $tokenData];
            } else {
                return ['error' => 'Token data missing in response.'];
            }
        } else {
            return ['error' => 'API error', 'status_code' => $httpCode, 'response' => json_decode($response, true)];
        }
    }

}

?>



