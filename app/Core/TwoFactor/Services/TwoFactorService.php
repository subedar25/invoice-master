<?php

namespace App\Core\TwoFactor\Services;

use App\Core\TwoFactor\Contracts\TwoFactorRepository;
use App\Models\User;

class TwoFactorService
{
    public function __construct(private TwoFactorRepository $repository) {}

    public function status(User $user): bool
    {
        return $this->repository->isEnabled($user);
    }

    public function startSetup(User $user): array
    {
        return $this->repository->createSetup($user);
    }

    public function confirm(User $user, string $code): bool
    {
        return $this->repository->confirm($user, $code);
    }

    public function recoveryCodes(User $user): array
    {
        return $this->repository->getRecoveryCodes($user);
    }

    public function regenerate(User $user): array
    {
        return $this->repository->regenerateRecoveryCodes($user);
    }

    public function disable(User $user): void
    {
        $this->repository->disable($user);
    }
}