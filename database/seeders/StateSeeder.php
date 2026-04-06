<?php

namespace Database\Seeders;

use App\Models\State;
use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['country_code' => 'MY', 'name' => 'Johor'],
            ['country_code' => 'MY', 'name' => 'Kedah'],
            ['country_code' => 'MY', 'name' => 'Kelantan'],
            ['country_code' => 'MY', 'name' => 'Melaka'],
            ['country_code' => 'MY', 'name' => 'Negeri Sembilan'],
            ['country_code' => 'MY', 'name' => 'Pahang'],
            ['country_code' => 'MY', 'name' => 'Perak'],
            ['country_code' => 'MY', 'name' => 'Perlis'],
            ['country_code' => 'MY', 'name' => 'Pulau Pinang'],
            ['country_code' => 'MY', 'name' => 'Sabah'],
            ['country_code' => 'MY', 'name' => 'Sarawak'],
            ['country_code' => 'MY', 'name' => 'Selangor'],
            ['country_code' => 'MY', 'name' => 'Terengganu'],
            ['country_code' => 'MY', 'name' => 'Wilayah Persekutuan'],

            ['country_code' => 'ID', 'name' => 'DKI Jakarta'],
            ['country_code' => 'ID', 'name' => 'Jawa Barat'],
            ['country_code' => 'ID', 'name' => 'Jawa Tengah'],
            ['country_code' => 'ID', 'name' => 'Jawa Timur'],
            ['country_code' => 'ID', 'name' => 'Bali'],
        ];

        foreach ($rows as $row) {
            State::query()->updateOrCreate(
                ['country_code' => $row['country_code'], 'name' => $row['name']],
                $row
            );
        }
    }
}

