<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'mobile' => fake()->unique()->phoneNumber(),
            'mobile_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'wallet_balance' => 0,
            'referral_code' => Str::random(8),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'mobile_verified_at' => null,
        ]);
    }

    public function configure()
    {
        return $this->afterCreating(function ($user) {
            $user->profile()->create(app(ProfileFactory::class)->definition());
        });
    }
}
