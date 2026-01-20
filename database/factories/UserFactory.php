<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
        $name = fake()->name();
        $role = fake()->randomElement(['customer', 'provider', 'affiliate']);
        $now = now();

        return [
            'name' => $name,
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => $now,
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'phone' => fake()->optional()->e164PhoneNumber(),
            'date_of_birth' => fake()->optional()->dateTimeBetween('-60 years', '-18 years'),
            'gender' => fake()->optional()->randomElement(['male', 'female', 'other']),
            'bio' => fake()->optional()->paragraph(),
            'avatar' => fake()->optional()->imageUrl(300, 300, 'people', true),
            'cover_image' => fake()->optional()->imageUrl(1200, 400, 'abstract', true),
            'website' => fake()->optional()->url(),
            'linkedin' => fake()->optional()->url(),
            'twitter' => fake()->optional()->url(),
            'youtube' => fake()->optional()->url(),
            'role' => $role,
            'is_verified' => fake()->boolean(90),
            'is_active' => fake()->boolean(95),
            'last_login_at' => fake()->optional()->dateTimeBetween('-30 days', 'now'),
            'preferences' => [],
            'sso_id' => (string) Str::uuid(),
            'sso_provider' => 'herime',
            'sso_metadata' => [
                'synced_at' => $now->toIso8601String(),
            ],
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
}
