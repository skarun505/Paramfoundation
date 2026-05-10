<?php

namespace Database\Seeders;

use App\Models\Slot;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SlotSeeder extends Seeder
{
    /**
     * Seed 5 sample slots for the next 3 days so the app is testable immediately.
     */
    public function run(): void
    {
        $slotTemplates = [
            ['label' => '09:00 AM – 10:00 AM', 'start_time' => '09:00', 'end_time' => '10:00', 'price' => 150],
            ['label' => '10:30 AM – 11:30 AM', 'start_time' => '10:30', 'end_time' => '11:30', 'price' => 150],
            ['label' => '12:00 PM – 01:00 PM', 'start_time' => '12:00', 'end_time' => '13:00', 'price' => 150],
            ['label' => '02:00 PM – 03:00 PM', 'start_time' => '14:00', 'end_time' => '15:00', 'price' => 200],
            ['label' => '03:30 PM – 04:30 PM', 'start_time' => '15:30', 'end_time' => '16:30', 'price' => 200],
        ];

        // Create slots for today + next 2 days
        foreach (range(0, 2) as $daysAhead) {
            $date = Carbon::today()->addDays($daysAhead)->toDateString();

            foreach ($slotTemplates as $template) {
                Slot::create([
                    'label'      => $template['label'],
                    'date'       => $date,
                    'start_time' => $template['start_time'],
                    'end_time'   => $template['end_time'],
                    'capacity'   => 50,
                    'booked'     => 0,
                    'price'      => $template['price'],
                    'is_active'  => true,
                ]);
            }
        }

        $this->command->info('✅ 15 sample slots created (5 per day × 3 days).');
    }
}
