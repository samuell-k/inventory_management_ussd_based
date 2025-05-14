<?php

require 'vendor/autoload.php';
use AfricasTalking\SDK\AfricasTalking;

class SmsService {
    protected $phone;
    protected $AT;
    private $apiKey = "atsk_0daf068ad8359cf4c0e3a97692dddd2964be0b86608fef97522423af5905c4afcf9afc3b";

    public function __construct($phone) {
        $this->phone = $phone;
        $this->AT = new AfricasTalking("sandbox", $this->apiKey);
    }

    public function sendSms($message, $recipients) {
        try {
            $sms = $this->AT->sms();
            $result = $sms->send([
                'username' => 'sandbox',
                'to' => $recipients,
                'message' => $message,
                'from' => "momoMoney"
            ]);
            return $result;
        } catch (Exception $e) {
            error_log("SMS Error: " . $e->getMessage());
            return false;
        }
    }
}


function sendSMS($phoneNumber, $message) {
    $smsService = new SmsService($phoneNumber);
    $result = $smsService->sendSms($message, $phoneNumber);
    
    if ($result && isset($result['status']) && $result['status'] === 'success') {
        return 'success';
    }
    return 'error';
} 