<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Permission;
use App\Notifications\NewUserNotification;
use App\Notifications\RoleUpdatedNotification;
use Illuminate\Support\Facades\Notification;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name'  => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
    public function changePassword(): static
    {
        return $this->state(fn (array $attributes) => [
            'change_password' => true,
        ]);
    }
    public function phone($phoneNumber): static
    {
        return $this->state(fn (array $attributes) => [
            'phone' => $phoneNumber,
        ]);
    }
    public function withRoles(array $roles): static
    {
        return $this->afterCreating(function ($user) use ($roles) {
            $user->assignRole($roles);
        });
    }
    public function withoutRoles(): static
    {
        return $this->afterCreating(function ($user) {
            $user->roles()->detach();
        });
    }
    public function withChangePassword($requiresChange = true): static
    {
        return $this->afterCreating(function ($user) use ($requiresChange) {
            $user->change_password = $requiresChange;
            $user->save();
        });
    }
    public function withoutChangePassword(): static
    {
        return $this->afterCreating(function ($user) {
            $user->change_password = false;
            $user->save();
        });
    }
   
    public function withoutPhone(): static
    {
        return $this->afterCreating(function ($user) {
            $user->phone = null;
            $user->save();
        });
    }
   public function withCustomPassword($password): static
    {
        return $this->afterCreating(function ($user) use ($password) {
            $user->password = Hash::make($password);
            $user->save();
        });
    }
    public function withoutCustomPassword(): static
    {
        return $this->afterCreating(function ($user) {
            $user->password = Hash::make('password');
            $user->save();
        });
    }
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => true,
        ]);
    }
    public function admin(): static
    {
        return $this->afterCreating(function ($user) {
            $user->assignRole('Admin');
        });
    }
    public function nonAdmin(): static
    {
        return $this->afterCreating(function ($user) {
            $user->roles()->detach();
        });
    }
    //Status notes saved as a factory state
    public function statusNotes($notes): static
    {
        return $this->state(fn (array $attributes) => [
            'status_notes' => $notes,
        ]);
    }
}
