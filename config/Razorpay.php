<?php

class RazorpayConfig {
    // Test Mode Credentials (Replace with your actual test credentials)
    const TEST_KEY_ID = 'rzp_test_1DP5mmOlF5G5ag';
    const TEST_KEY_SECRET = 'thisissecretkey';
    
    // Production Mode Credentials (Keep empty for now)
    const LIVE_KEY_ID = '';
    const LIVE_KEY_SECRET = '';
    
    // Environment (test/live)
    const ENVIRONMENT = 'test';
    
    // Currency
    const CURRENCY = 'INR';
    
    // Company Details
    const COMPANY_NAME = 'ElectroHub';
    const COMPANY_LOGO = 'https://your-domain.com/logo.png';
    const COMPANY_DESCRIPTION = 'Premium Electronic Accessories';
    
    public static function getKeyId() {
        return self::ENVIRONMENT === 'test' ? self::TEST_KEY_ID : self::LIVE_KEY_ID;
    }
    
    public static function getKeySecret() {
        return self::ENVIRONMENT === 'test' ? self::TEST_KEY_SECRET : self::LIVE_KEY_SECRET;
    }
    
    public static function isTestMode() {
        return self::ENVIRONMENT === 'test';
    }
    
    public static function getConfig() {
        return [
            'key_id' => self::getKeyId(),
            'key_secret' => self::getKeySecret(),
            'currency' => self::CURRENCY,
            'company_name' => self::COMPANY_NAME,
            'company_logo' => self::COMPANY_LOGO,
            'company_description' => self::COMPANY_DESCRIPTION,
            'test_mode' => self::isTestMode()
        ];
    }
}
?>
