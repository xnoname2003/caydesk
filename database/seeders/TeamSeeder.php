<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Team;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = [
            'Technical Support',
            'Customer Service',
            'Billing & Finance',
            'Infrastructure',
        ];

        foreach ($teams as $teamName) {
            Team::create([
                'name' => $teamName,
            ]);
        }
    }
}
