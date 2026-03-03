<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use App\Core\Email\Services\EmailService;
use Illuminate\Support\Carbon;

class PasswordResetLinkController extends Controller
{
    private EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {

        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __('passwords.user')]);
        }

        // Generate password reset token
        $token = Password::createToken($user);

        // Create reset URL with real token
        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $request->email,
        ]);

        // Send custom password reset email
        $this->sendPasswordResetEmail($user, $resetUrl);

        return back()->with('status', __('passwords.sent'));
    }

    /**
     * Send password reset email to the user.
     */
    private function sendPasswordResetEmail(User $user, string $resetUrl): void
    {
        $subject = 'Reset Your Password - ' . config('app.name');
        $view = 'masterapp.emails.password-reset';

        $data = [
            'userName' => $user->first_name . ' ' . $user->last_name,
            'appName'  => config('app.name'),
            'resetUrl' => $resetUrl,
        ];

        $options = [];

        $this->emailService->send(
            $user->email,
            $subject,
            $view,
            $data,
            $options
        );
    }
}
