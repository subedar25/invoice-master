<?php

namespace App\Http\Livewire\MasterApp\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Core\TwoFactor\Services\TwoFactorService;
use Illuminate\Validation\ValidationException;

class TwoFactor extends Component
{
    public $enabled = false;
    public $showSetup = false;
    public $qr, $secret, $recoveryCodes = [], $code;
    public $password;
    public $confirmingDisable = false;


    public function boot(TwoFactorService $service)
    {
        $this->service = $service;
    }

    public function render()
    {
        return view('masterapp.livewire.settings.two-factor');
    }

    public function mount()
    {
        $this->authorize('enable-two-factor');

        $user = Auth::user();

        $this->enabled = $this->service->status($user);

        if ($this->enabled) {
            $this->recoveryCodes = $this->service->recoveryCodes($user);
        }
    }

    public function startSetup()
    {
        $data = $this->service->startSetup(Auth::user());

        $this->qr = $data['qr'];
        $this->secret = $data['secret'];
        $this->showSetup = true;
    }

    public function confirm()
    {
        $this->validate(['code' => 'required|digits:6']);

        if (! $this->service->confirm(Auth::user(), $this->code)) {
            $this->addError('code', 'Invalid OTP');
            return;
        }

        $this->enabled = true;
        $this->showSetup = false;
        $this->recoveryCodes = $this->service->recoveryCodes(Auth::user());
        $this->code = null;
    }

    public function regenerate()
    {
        $this->recoveryCodes = $this->service->regenerate(Auth::user());
    }

    // public function disable()
    // {
    //     $this->service->disable(Auth::user());

    //     $this->reset(['enabled','showSetup','qr','secret','recoveryCodes','code']);
    // }

    public function askDisable()
    {
        $this->confirmingDisable = true;
    }

    public function confirmDisable()
    {
        $this->validate([
            'password' => ['required', 'current_password'], 
            // OR use just 'required' if you want service to handle validation
        ]);

        try {
            $this->service->disable(Auth::user(), $this->password);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
            return;
        }

        $this->reset([
            'enabled',
            'showSetup',
            'qr',
            'secret',
            'recoveryCodes',
            'code',
            'password',
            'confirmingDisable'
        ]);
    }

    public function disable(User $user, string $password): void
    {
        if (! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Incorrect password.'],
            ]);
        }

        $this->repository->disable($user);
    }

}