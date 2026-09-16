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
     * Laravel's skeleton default (name/email/email_verified_at) doesn't
     * match this app's actual `users` table — user_id and role are
     * required (NOT NULL, no default) and there is no email_verified_at
     * column at all (App\Models\User doesn't implement MustVerifyEmail).
     * Left unadapted, User::factory()->create() fails against the real
     * schema, which is what every permission test in
     * tests/Feature/Chat/RetrievalPermissionTest.php relies on to create
     * student/instructor/admin rows.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => fake()->unique()->numerify('##-#####'),
            'role' => 'student',
            'email' => fake()->unique()->safeEmail(),
            'name' => fake()->name(),
            'year_level' => null,
            'account_status' => 'active',
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'role' => 'admin',
            'user_id' => 'ADMIN-'.fake()->unique()->numerify('###'),
        ]);
    }

    public function instructor(): static
    {
        return $this->state(fn () => ['role' => 'instructor']);
    }

    public function student(): static
    {
        return $this->state(fn () => ['role' => 'student']);
    }
}
