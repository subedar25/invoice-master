<?php

namespace App\Infrastructure\Persistence\TwoFactor;

use App\Models\User;
use App\Core\TwoFactor\Contracts\TwoFactorRepository;

class EloquentTwoFactorRepository implements TwoFactorRepository
{
    public function isEnabled(User $user): bool
    {
        return $user->hasTwoFactorEnabled();
    }

    public function createSetup(User $user): array
    {
        $totp = $user->createTwoFactorAuth();

        return [
            'qr' => $totp->toQr(200),
            'secret' => $totp->toString(),
        ];
    }

    public function confirm(User $user, string $code): bool
    {
        return $user->confirmTwoFactorAuth($code);
    }

    public function getRecoveryCodes(User $user): array
    {
        return $user->getRecoveryCodes()->toArray();
    }

    public function regenerateRecoveryCodes(User $user): array
    {
        return $user->generateRecoveryCodes()->toArray();
    }

    public function disable(User $user): void
    {
        $user->disableTwoFactorAuth();
    }
}
