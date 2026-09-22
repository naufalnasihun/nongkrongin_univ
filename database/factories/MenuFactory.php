<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MenuFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama_menu' => fake()->words(2, true),
            'foto' => null,
            'harga' => fake()->numberBetween(5000, 30000),
            'deskripsi' => fake()->sentence(),
            'ketersediaan' => true,
        ];
    }
}