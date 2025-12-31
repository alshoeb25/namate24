<?php

namespace Database\Seeders;

use App\Models\FieldLabel;
use Illuminate\Database\Seeder;

class FieldLabelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $labels = [
            // Service Type Labels
            ['field_name' => 'service_type', 'field_value' => 'tutoring', 'label' => 'Tutoring', 'category' => 'requirement', 'order' => 1],
            ['field_name' => 'service_type', 'field_value' => 'assignment_help', 'label' => 'Assignment Help', 'category' => 'requirement', 'order' => 2],

            // Budget Type Labels
            ['field_name' => 'budget_type', 'field_value' => 'fixed', 'label' => 'Fixed', 'category' => 'requirement', 'order' => 1],
            ['field_name' => 'budget_type', 'field_value' => 'per_hour', 'label' => 'Per Hour', 'category' => 'requirement', 'order' => 2],
            ['field_name' => 'budget_type', 'field_value' => 'per_day', 'label' => 'Per Day', 'category' => 'requirement', 'order' => 3],
            ['field_name' => 'budget_type', 'field_value' => 'per_week', 'label' => 'Per Week', 'category' => 'requirement', 'order' => 4],
            ['field_name' => 'budget_type', 'field_value' => 'per_month', 'label' => 'Per Month', 'category' => 'requirement', 'order' => 5],
            ['field_name' => 'budget_type', 'field_value' => 'per_year', 'label' => 'Per Year', 'category' => 'requirement', 'order' => 6],

            // Gender Preference Labels
            ['field_name' => 'gender_preference', 'field_value' => 'no_preference', 'label' => 'No Preference', 'category' => 'requirement', 'order' => 1],
            ['field_name' => 'gender_preference', 'field_value' => 'preferably_male', 'label' => 'Preferably Male', 'category' => 'requirement', 'order' => 2],
            ['field_name' => 'gender_preference', 'field_value' => 'preferably_female', 'label' => 'Preferably Female', 'category' => 'requirement', 'order' => 3],
            ['field_name' => 'gender_preference', 'field_value' => 'only_male', 'label' => 'Only Male', 'category' => 'requirement', 'order' => 4],
            ['field_name' => 'gender_preference', 'field_value' => 'only_female', 'label' => 'Only Female', 'category' => 'requirement', 'order' => 5],

            // Availability Labels
            ['field_name' => 'availability', 'field_value' => 'part_time', 'label' => 'Part Time', 'category' => 'requirement', 'order' => 1],
            ['field_name' => 'availability', 'field_value' => 'full_time', 'label' => 'Full Time', 'category' => 'requirement', 'order' => 2],

            // Meeting Options Labels
            ['field_name' => 'meeting_options', 'field_value' => 'online', 'label' => 'Online', 'category' => 'requirement', 'order' => 1],
            ['field_name' => 'meeting_options', 'field_value' => 'at_my_place', 'label' => 'At Student\'s Place', 'category' => 'requirement', 'order' => 2],
            ['field_name' => 'meeting_options', 'field_value' => 'travel_to_tutor', 'label' => 'At Tutor\'s Place', 'category' => 'requirement', 'order' => 3],

            // Status Labels
            ['field_name' => 'status', 'field_value' => 'active', 'label' => 'Active', 'category' => 'requirement', 'order' => 1],
            ['field_name' => 'status', 'field_value' => 'paused', 'label' => 'Paused', 'category' => 'requirement', 'order' => 2],
            ['field_name' => 'status', 'field_value' => 'closed', 'label' => 'Closed', 'category' => 'requirement', 'order' => 3],

            // Lead Status Labels
            ['field_name' => 'lead_status', 'field_value' => 'open', 'label' => 'Open', 'category' => 'requirement', 'order' => 1],
            ['field_name' => 'lead_status', 'field_value' => 'full', 'label' => 'Full', 'category' => 'requirement', 'order' => 2],
            ['field_name' => 'lead_status', 'field_value' => 'closed', 'label' => 'Closed', 'category' => 'requirement', 'order' => 3],

            // Tutor Location Preference Labels
            ['field_name' => 'tutor_location_preference', 'field_value' => 'all_countries', 'label' => 'All Countries', 'category' => 'requirement', 'order' => 1],
            ['field_name' => 'tutor_location_preference', 'field_value' => 'india_only', 'label' => 'India Only', 'category' => 'requirement', 'order' => 2],
        ];

        foreach ($labels as $label) {
            FieldLabel::updateOrCreate(
                [
                    'field_name' => $label['field_name'],
                    'field_value' => $label['field_value'],
                ],
                $label
            );
        }

        $this->command->info('Field labels seeded successfully!');
    }
}

