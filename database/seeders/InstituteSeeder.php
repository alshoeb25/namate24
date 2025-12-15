<?php

namespace Database\Seeders;

use App\Models\Institute;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class InstituteSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/ugc_universities_with_region.csv');

        if (!File::exists($path)) {
            $this->command->error('UGC CSV file not found!');
            return;
        }

        $rows = array_map('str_getcsv', file($path));
        $rows = array_filter($rows, fn ($row) => count(array_filter($row)) > 0);

        $header = array_map(fn ($h) => trim(Str::lower($h)), array_shift($rows));
        $headerCount = count($header);

        $inserted = 0;
        $skipped = 0;

        foreach ($rows as $row) {

            // Normalize column count
            if (count($row) < $headerCount) {
                $row = array_pad($row, $headerCount, null);
            } elseif (count($row) > $headerCount) {
                $row = array_slice($row, 0, $headerCount);
            }

            $data = array_combine($header, $row);

            // 🔴 HARD VALIDATION
            if (empty($data['university_name'])) {
                $skipped++;
                continue;
            }

            Institute::updateOrCreate(
                [
                    'ugc_code' => $data['ugc_code'] ?? null,
                ],
                [
                    'name'            => trim($data['university_name']),
                    'type'            => $data['university_type'] ?? null,
                    'state'           => $data['state'] ?? null,
                    'city'            => $data['city_guess'] ?? null,
                    'region'          => $data['region'] ?? null,
                    'is_ugc_approved' => true,
                    'status'          => true,
                ]
            );

            $inserted++;
        }

        $this->command->info("UGC Institutes Imported: {$inserted}");
        $this->command->warn("Skipped Invalid Rows: {$skipped}");
    }
}
