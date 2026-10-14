<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MenuFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama_menu' => fake()->words(2, true),
            'kategori' => fake()->randomElement(['makanan', 'minuman', 'snack', 'dessert']),
            'foto' => null,
            'harga' => fake()->numberBetween(5000, 30000),
            'stok' => fake()->numberBetween(0, 50),
            'deskripsi' => fake()->sentence(),
            'ketersediaan' => true,
        ];
    }

    public function habis(): static
    {
        return $this->state(fn () => ['stok' => 0, 'ketersediaan' => false]);
    }
}
