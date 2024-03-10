<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RegisterLog>
 */
class RegisterLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "ip" => $this->faker->ipv4(),
            "user_agent" => $this->faker->userAgent(),
            "header" => $this->faker->url(),
            "query_params" => "teste=" . $this->faker->word(5),
            "redirect_id" => 1,
            "created_at" => $this->faker->dateTimeThisYear(),
            "updated_at" => $this->faker->dateTimeThisYear(),
        ];
    }
}
