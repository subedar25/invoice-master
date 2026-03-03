<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;

class TwoFactorController
{
    public function setup(Request $request)
    {
        $secret = $request->user()->createTwoFactorAuth();

        return view('auth.2fa.setup', [
            'qr' => $secret->toQr(),
            'secret' => $secret->toString(),
        ]);
    }

    public function confirm(Request $request)
    {
        $request->validate(['code' => 'required|numeric']);

        if (! $request->user()->confirmTwoFactorAuth($request->code)) {
            return back()->withErrors(['code' => 'Invalid OTP']);
        }

        // Recovery codes generated automatically
        $codes = $request->user()->getRecoveryCodes();
        return view('auth.2fa.recovery-codes', compact('codes'));
    }

    public function regenerateCodes(Request $request)
    {
        $codes = $request->user()->generateRecoveryCodes();

        return view('auth.2fa.recovery-codes', compact('codes'));
    }

    public function disable(Request $request)
    {
        $request->user()->disableTwoFactorAuth();
        return redirect()->route('profile')->with('success', '2FA disabled');
    }
}
