<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{

    public function login(Request $request){
        return view("auth.login");
    }

    public function generate(Request $request){
        $request->validate([
            'mobile' =>'required|exists:users,mobile',
        ]);

        $userOtp = $this->genereateOTP($request->mobile);
        $userOtp->sendSms($request->mobile);
        // return redirect()->back()->with('message','Otp sent successfully!');
        return redirect()->route('verify', ['user_id' => $userOtp->user_id])->with('success', 'Otp sent successfully!');
    }

    public function genereateOTP($mobile)
    {
        $user = User::where('mobile', $mobile)->first();
        $userOtp = UserOtp::where('user_id', $user->id)->latest()->first();

        $now = now();

        if ($userOtp && $now->isBefore($userOtp->expires_at)) {
            return $userOtp;
        }

        return UserOtp::create([
            'user_id'=> $user->id,
            'otp' => rand(100000, 999999),
            'expires_at' => now()->addMinutes(10)
        ]);
    }

    public function verify($user_id){
        return view("auth.verify")->with([
            'user_id'=>$user_id
        ]);
        // return view('auth.verify', compact('user_id'));
    }



    public function loginWithOtp(Request $request){
        $request->validate([
            'user_id' =>'required|exists:users,id',
            'otp' =>'required',
        ]);

        $userOtp = UserOtp::where('user_id', $request->user_id)->where('otp', $request->otp)->first();

        $now = now();
        if (!$userOtp) {
            return redirect()->back()->with('error', 'Invalid OTP!');
        } else if($userOtp && $now->isAfter($userOtp->expires_at)) {
            return redirect()->back()->with('error','Your OTP has expired! Please generate a new one.');
        }

        $user = User::whereId($request->user_id)->first();
       
        if ($user) {
            $userOtp->update([
                'expires_at' => now()
            ]);

            Auth::login($user);
            return redirect();
        }

        return redirect()->route('login')->with('error','otp not correct');
    }
}