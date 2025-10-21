<?php

require_once 'config/Razorpay.php';

class RazorpayPayment {
    private $keyId;
    private $keySecret;
    private $currency;
    
    public function __construct() {
        $config = RazorpayConfig::getConfig();
        $this->keyId = $config['key_id'];
        $this->keySecret = $config['key_secret'];
        $this->currency = $config['currency'];
    }
    
    /**
     * Create Razorpay order
     */
    public function createOrder($amount, $orderId, $customerDetails = []) {
        $url = 'https://api.razorpay.com/v1/orders';
        
        $data = [
            'amount' => $amount * 100, // Amount in paise
            'currency' => $this->currency,
            'receipt' => $orderId,
            'payment_capture' => 1
        ];
        
        // Add customer notes if provided
        if (!empty($customerDetails)) {
            $data['notes'] = [
                'customer_name' => $customerDetails['name'] ?? '',
                'customer_email' => $customerDetails['email'] ?? '',
                'customer_phone' => $customerDetails['phone'] ?? ''
            ];
        }
        
        $response = $this->makeApiCall($url, $data);
        
        if ($response && isset($response['id'])) {
            return [
                'success' => true,
                'order_id' => $response['id'],
                'amount' => $response['amount'],
                'currency' => $response['currency'],
                'receipt' => $response['receipt']
            ];
        }
        
        return [
            'success' => false,
            'error' => $response['error']['description'] ?? 'Failed to create order'
        ];
    }
    
    /**
     * Verify payment signature
     */
    public function verifyPayment($razorpayOrderId, $razorpayPaymentId, $razorpaySignature) {
        $body = $razorpayOrderId . "|" . $razorpayPaymentId;
        $expectedSignature = hash_hmac('sha256', $body, $this->keySecret);
        
        return hash_equals($expectedSignature, $razorpaySignature);
    }
    
    /**
     * Get payment details
     */
    public function getPaymentDetails($paymentId) {
        $url = "https://api.razorpay.com/v1/payments/{$paymentId}";
        
        $response = $this->makeApiCall($url, null, 'GET');
        
        if ($response && isset($response['id'])) {
            return [
                'success' => true,
                'payment' => $response
            ];
        }
        
        return [
            'success' => false,
            'error' => $response['error']['description'] ?? 'Failed to fetch payment details'
        ];
    }
    
    /**
     * Refund payment
     */
    public function refundPayment($paymentId, $amount = null, $notes = []) {
        $url = "https://api.razorpay.com/v1/payments/{$paymentId}/refund";
        
        $data = [];
        if ($amount) {
            $data['amount'] = $amount * 100; // Amount in paise
        }
        if (!empty($notes)) {
            $data['notes'] = $notes;
        }
        
        $response = $this->makeApiCall($url, $data);
        
        if ($response && isset($response['id'])) {
            return [
                'success' => true,
                'refund' => $response
            ];
        }
        
        return [
            'success' => false,
            'error' => $response['error']['description'] ?? 'Failed to process refund'
        ];
    }
    
    /**
     * Make API call to Razorpay
     */
    private function makeApiCall($url, $data = null, $method = 'POST') {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->keyId . ':' . $this->keySecret);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        
        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response === false) {
            return ['error' => ['description' => 'Network error occurred']];
        }
        
        $decodedResponse = json_decode($response, true);
        
        if ($httpCode >= 400) {
            return $decodedResponse;
        }
        
        return $decodedResponse;
    }
    
    /**
     * Generate payment form data
     */
    public function generatePaymentData($orderId, $amount, $customerDetails, $orderDetails = []) {
        $config = RazorpayConfig::getConfig();
        
        return [
            'key' => $this->keyId,
            'amount' => $amount * 100, // Amount in paise
            'currency' => $this->currency,
            'order_id' => $orderId,
            'name' => $config['company_name'],
            'description' => $orderDetails['description'] ?? $config['company_description'],
            'image' => $config['company_logo'],
            'prefill' => [
                'name' => $customerDetails['name'] ?? '',
                'email' => $customerDetails['email'] ?? '',
                'contact' => $customerDetails['phone'] ?? ''
            ],
            'theme' => [
                'color' => '#6366f1'
            ],
            'modal' => [
                'ondismiss' => 'function(){console.log("Payment cancelled")}'
            ]
        ];
    }
}
?>
