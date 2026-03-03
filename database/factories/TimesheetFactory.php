<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Timesheet;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Timesheet>
 */
class TimesheetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Timesheet::class;

    public function definition(): array
    {
        $start = Carbon::now()->subHours(2);

        return [
            'user_id'       => User::factory(),
            'start_time'    => $start,
            'end_time'      => $start->copy()->addHours(2),
            'clock_in_mode' => 'office',
            'type'          => 'normal_paid',
            'notes'         => 'Test timesheet entry',
        ];
    }

    /**
     * Open (running) shift
     */
    public function open(): static
    {
        return $this->state(fn () => [
            'end_time' => null,
        ]);
    }

    /**
     * Invalid (end before start) — used to test auto-heal
     */
    public function invalid(): static
    {
        return $this->state(fn () => [
            'start_time' => Carbon::now()->subHour(),
            'end_time'   => Carbon::now()->subHours(2),
        ]);
    }
}
