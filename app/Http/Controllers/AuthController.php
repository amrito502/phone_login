<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Show the login form
    public function login(Request $request)
    {
        return view("auth.login");
    }

    // Generate and send OTP
    public function generate(Request $request)
    {
        $request->validate([
            'mobile' => 'required|exists:users,mobile',
        ]);

        // Generate OTP and send via SMS
        $userOtp = $this->generateOTP($request->mobile);
        $userOtp->sendSms($request->mobile);

        return redirect()->route('verify', ['user_id' => $userOtp->user_id])
                         ->with('success', 'Otp sent successfully!');
    }

    // Generate OTP for user
    public function generateOTP($mobile)
    {
        $user = User::where('mobile', $mobile)->first();

        // If no user is found, return an error
        if (!$user) {
            return redirect()->back()->with('error', 'User not found');
        }

        $userOtp = UserOtp::where('user_id', $user->id)->latest()->first();
        $now = now();

        // Return existing OTP if it's still valid
        if ($userOtp && $now->isBefore($userOtp->expires_at)) {
            return $userOtp;
        }

        // Create and return a new OTP if the old one is expired or doesn't exist
        return UserOtp::create([
            'user_id' => $user->id,
            'otp' => rand(100000, 999999), // Random OTP
            'expires_at' => now()->addMinutes(10), // OTP expires in 10 minutes
        ]);
    }

    // Show the OTP verification page
    public function verify($user_id)
    {
        return view("auth.verify", compact('user_id'));
    }

    // Login with OTP
    public function loginWithOtp(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'otp' => 'required',
        ]);

        // Validate the OTP
        $userOtp = UserOtp::where('user_id', $request->user_id)
                          ->where('otp', $request->otp)
                          ->first();

        $now = now();

        // Handle invalid OTP
        if (!$userOtp) {
            return redirect()->back()->with('error', 'Invalid OTP!');
        }

        // Handle expired OTP
        if ($now->isAfter($userOtp->expires_at)) {
            return redirect()->back()->with('error', 'Your OTP has expired! Please generate a new one.');
        }

        // Log the user in
        $user = User::find($request->user_id);

        if ($user) {
            // Mark the OTP as used by setting its expiration to the current time
            $userOtp->update(['expires_at' => now()]);

            // Log the user in
            Auth::login($user);

            // Redirect to the homepage or a secure page after login
            return redirect()->route('home'); // Adjust route as needed
        }

        return redirect()->route('login')->with('error', 'OTP not correct');
    }
}
