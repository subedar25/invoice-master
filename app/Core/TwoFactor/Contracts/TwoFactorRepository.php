<?php

namespace App\Core\TwoFactor\Contracts;

use App\Models\User;

interface TwoFactorRepository
{
    public function isEnabled(User $user): bool;

    public function createSetup(User $user): array;

    public function confirm(User $user, string $code): bool;

    public function getRecoveryCodes(User $user): array;

    public function regenerateRecoveryCodes(User $user): array;

    public function disable(User $user): void;
}