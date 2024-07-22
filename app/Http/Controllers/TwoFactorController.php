<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;

class TwoFactorController extends Controller
{
    public function index()
    {
        return view('auth.verify');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = auth()->user();

        // Periksa apakah kode sudah kadaluarsa
        if ($user->expire_at && $user->expire_at < now()) {
            return redirect()->back()->withErrors(['code' => 'Kode OTP sudah kadaluarsa. Silakan minta kode baru.']);
        }

        if ($request->input('code') == $user->code) {
            // Reset code sebelum redirect
            $user->resetCode();

            // Redirect ke route 'home'
            return redirect()->route('dashboard');
        }

        return redirect()->back()->withErrors(['code' => 'Kode yang anda masukkan salah']);
    }

    public function resend(Request $request)
    {
        $user = auth()->user();
        $user->generateCode();

        // Kirim OTP melalui WhatsApp
        $message = "Jangan Beritahu kode ini kepada siapapun termasuk teman terdekat anda. Jaga Rahasia kode ini, OTP : " . $user->code;

        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $twilio = new Client($sid, $token);

        $twilio->messages->create(
            'whatsapp:' . $user->phone,
            [
                'from' => 'whatsapp:' . config('services.twilio.twilio_number'),
                'body' => $message,
            ]
        );

        return response()->json(['message' => 'OTP telah dikirim ulang']);
    }
}
