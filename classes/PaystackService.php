<?php

declare(strict_types=1);

namespace App;

/**
 * Paystack Payment Service
 */
class PaystackService
{
    private string $secretKey;
    private string $publicKey;
    private string $baseUrl;
    
    public function __construct()
    {
        $config = require __DIR__ . '/../settings/config.php';
        $this->secretKey = $config['payment']['paystack_secret_key'] ?? '';
        $this->publicKey = $config['payment']['paystack_public_key'] ?? '';
        
        // Fallback: If keys are empty, try to read directly from .env file
        if (empty($this->publicKey) || empty($this->secretKey)) {
            $envFile = __DIR__ . '/../.env';
            if (file_exists($envFile)) {
                $envContent = file_get_contents($envFile);
                
                if (empty($this->publicKey) && preg_match('/PAYSTACK_PUBLIC_KEY\s*=\s*(.+)/', $envContent, $matches)) {
                    $this->publicKey = trim($matches[1]);
                }
                
                if (empty($this->secretKey) && preg_match('/PAYSTACK_SECRET_KEY\s*=\s*(.+)/', $envContent, $matches)) {
                    $this->secretKey = trim($matches[1]);
                }
            }
        }
        
        // Final fallback: Use test keys if still empty (for development only)
        if (empty($this->publicKey)) {
            $this->publicKey = 'pk_test_042e80c17c891462d6f0b7f651b48745c184a1b5';
        }
        if (empty($this->secretKey)) {
            $this->secretKey = 'sk_test_902b672b93a30e43809bc151fc4e21c73020b72f';
        }
        
        $this->baseUrl = ($config['payment']['mode'] ?? 'mock') === 'live' 
            ? 'https://api.paystack.co' 
            : 'https://api.paystack.co'; // Use test mode by default
    }
    
    /**
     * Get public key for frontend
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }
    
    /**
     * Initialize payment transaction
     */
    public function initializeTransaction(array $data): array
    {
        // If in mock mode or no keys, return mock response
        if (empty($this->secretKey) || empty($this->publicKey)) {
            // Get base path from environment or default
            $basePath = $_ENV['APP_BASE_PATH'] ?? '/';
            $basePath = '/' . trim($basePath, '/');
            $basePath = $basePath === '/' ? '/' : $basePath . '/';
            
            return [
                'status' => true,
                'message' => 'Mock transaction initialized',
                'data' => [
                    'authorization_url' => $basePath . 'payment/mock-callback?reference=MOCK-' . bin2hex(random_bytes(8)),
                    'access_code' => 'MOCK-' . bin2hex(random_bytes(8)),
                    'reference' => 'MOCK-' . bin2hex(random_bytes(8))
                ]
            ];
        }
        
        $url = $this->baseUrl . '/transaction/initialize';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->secretKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new \Exception('Paystack API Error: ' . $error);
        }
        
        $result = json_decode($response, true);
        
        if (!$result || !isset($result['status'])) {
            throw new \Exception('Invalid response from Paystack');
        }
        
        return $result;
    }
    
    /**
     * Verify payment transaction
     */
    public function verifyTransaction(string $reference): array
    {
        // If in mock mode, return mock success
        if (empty($this->secretKey)) {
            return [
                'status' => true,
                'message' => 'Mock transaction verified',
                'data' => [
                    'status' => 'success',
                    'reference' => $reference,
                    'amount' => 0,
                    'currency' => 'GHS',
                    'customer' => ['email' => 'mock@example.com'],
                    'paid_at' => date('Y-m-d H:i:s')
                ]
            ];
        }
        
        $url = $this->baseUrl . '/transaction/verify/' . $reference;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->secretKey
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new \Exception('Paystack API Error: ' . $error);
        }
        
        $result = json_decode($response, true);
        
        if (!$result || !isset($result['status'])) {
            throw new \Exception('Invalid response from Paystack');
        }
        
        return $result;
    }
    
    /**
     * Create customer
     */
    public function createCustomer(string $email, string $firstName, string $lastName, ?string $phone = null): array
    {
        if (empty($this->secretKey)) {
            return [
                'status' => true,
                'data' => [
                    'customer_code' => 'MOCK-' . bin2hex(random_bytes(8)),
                    'email' => $email
                ]
            ];
        }
        
        $url = $this->baseUrl . '/customer';
        
        $data = [
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName
        ];
        
        if ($phone) {
            $data['phone'] = $phone;
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->secretKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true) ?: ['status' => false];
    }
}

