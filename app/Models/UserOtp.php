<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Twilio\Rest\Client;
use Exception;

class UserOtp extends Model
{
    protected $fillable = [
        'otp',
        'expires_at',
        'user_id',
    ];

    // Send OTP via SMS
    public function sendSms($receiver_number)
    {
        $message = "Your verification code is: " . $this->otp;

        try {
            // Get Twilio credentials from .env
            $account_sid = env('TWILIO_SID');
            $auth_token = env('TWILIO_TOKEN');
            $twilio_number = env('TWILIO_FROM');
            
            // Initialize Twilio client
            $client = new Client($account_sid, $auth_token);

            // Send the SMS
            $client->messages->create(
                $receiver_number,
                [
                    'from' => $twilio_number,
                    'body' => $message,
                ]
            );

            // Log the successful SMS send
            info("SMS sent successfully to: " . $receiver_number);
            return true; // Return true on success

        } catch (Exception $e) {
            // Log error message if SMS sending fails
            info("SMS sending failed: " . $e->getMessage());
            return false; // Return false on failure
        }
    }
}
