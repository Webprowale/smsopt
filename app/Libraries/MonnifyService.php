<?php

namespace App\Libraries;

use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class MonnifyService
{
    protected $base_url;
    protected $api_key;
    protected $secret_key;
    protected $contract_code;

    public function __construct()
    {
        $this->base_url = "https://sandbox.monnify.com/api/v1"; 
        $this->api_key = getenv('MONNIFY_API_KEY');
        $this->secret_key = getenv('MONNIFY_SECRET_KEY');
        $this->contract_code = getenv('MONNIFY_CONTRACT_CODE');
    }

    private function generateAccessToken()
    {
        $credentials = base64_encode($this->api_key . ':' . $this->secret_key);
        $client = service('curlrequest');
        $response = $client->request('POST', $this->base_url . '/auth/login', [
            'headers' => [
                'Authorization' => 'Basic ' . $credentials
            ]
        ]);

        $body = json_decode($response->getBody(), true);
        if ($response->getStatusCode() === 200 && isset($body['responseBody']['accessToken'])) {
            return $body['responseBody']['accessToken'];
        } 
            throw new \Exception('Failed to generate access token from Monnify.');
    
    }

    // Create a reserved wallet
    public function createWallet($customerEmail, $customerName)
    {
        $accessToken = $this->generateAccessToken();

        $client = service('curlrequest');
        $response = $client->request('POST', $this->base_url . '/bank-transfer/reserved-accounts', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'contractCode' => $this->contract_code,
                'accountReference' => uniqid(),
                'accountName' => $customerName,
                'customerEmail' => $customerEmail,
                'currencyCode' => 'NGN',
                'incomeSplitConfig' => [],
            ]
        ]);

        $body = json_decode($response->getBody(), true);

        if ($response->getStatusCode() === 200 && $body['responseCode'] === "0") {
            return $body['responseBody'];
        }
            throw new \Exception('Failed to create wallet. ' . $body['responseMessage']);
        
    }
}
