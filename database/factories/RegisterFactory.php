<?php

namespace Database\Factories;

use Hashids\Hashids;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class RegisterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $hashids = new Hashids('', 6);

        return [
            "status" => 1,
            "url" => $this->faker->url(),
            "code" => $hashids->encode(random_int(1, 100)),
            "last_access" => $this->faker->dateTimeThisYear(),
            "created_at" => $this->faker->dateTimeThisYear(),
            "updated_at" => $this->faker->dateTimeThisYear(),
        ];
    }
}
