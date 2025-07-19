<?php

namespace Horlerdipo\SimpleOtp\Database\Factories;

use Horlerdipo\SimpleOtp\Enums\ChannelType;
use Horlerdipo\SimpleOtp\Models\Otp;
use Illuminate\Database\Eloquent\Factories\Factory;

class OtpFactory extends Factory
{
    protected $model = Otp::class;

    public function definition(): array
    {
        return [
            'destination' => $this->faker->email(),
            'destination_type' => $this->faker->randomElement(ChannelType::cases()),
            'purpose' => $this->faker->word(),
            'token' => $this->faker->word(),
            'expires_at' => $this->faker->date(),
            'is_used' => $this->faker->boolean(),
            'is_hashed' => $this->faker->boolean(),
        ];
    }
}
