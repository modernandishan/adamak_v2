<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{

    public function definition(): array
    {
        $relationships = ['پدر', 'مادر', 'فرزند', 'همسر', 'خود فرد'];

        return [
            'user_id' => User::factory(),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'relationship' => $this->faker->randomElement($relationships),
            'province' => $this->faker->city,
            'city' => $this->faker->city,
            'address' => $this->faker->address,
            'postal_code' => $this->faker->numerify('##########'),
            'birth_date' => $this->faker->date,
            'national_code' => $this->faker->unique()->numerify('##########'),
            'education_level' => $this->faker->randomElement(['دیپلم', 'کارشناسی', 'کارشناسی ارشد', 'دکتری']),
        ];
    }
}
