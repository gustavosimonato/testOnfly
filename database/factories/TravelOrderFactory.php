<?php

namespace Database\Factories;

use App\Models\TravelOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class TravelOrderFactory extends Factory
{
    protected $model = TravelOrder::class;

    public function definition()
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'destination' => $this->faker->city,
            'departure_date' => $this->faker->dateTimeBetween('now', '+1 week'),
            'return_date' => $this->faker->dateTimeBetween('+1 week', '+2 weeks'),
            'status' => 'requested',
        ];
    }
}
