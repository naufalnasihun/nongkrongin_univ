<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nim' => fake()->unique()->numerify('2495114###'),
            'nama' => fake()->name(),
            'kelas' => fake()->randomElement(['TI-A', 'TI-B', 'TI-C', 'Karyawan']),
        ];
    }
}