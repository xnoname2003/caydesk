<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Priority;
use App\Models\SlaRule;

class SlaRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $priorities = Priority::all();

        if ($priorities->isEmpty()) {
            return;
        }

        foreach ($priorities as $priority) {
            $name = 'SLA ' . $priority->name;
            $response_time = 24;
            $resolution_time = 72;

            if ($priority->name === 'Critical') {
                $response_time = 1; // 1 jam harus direspons
                $resolution_time = 4; // 4 jam harus beres
            } elseif ($priority->name === 'High') {
                $response_time = 2;
                $resolution_time = 8;
            } elseif ($priority->name === 'Medium') {
                $response_time = 4;
                $resolution_time = 24;
            }

            SlaRule::create([
                'name' => $name,
                'priority_id' => $priority->id,
                'response_time_hours' => $response_time,
                'resolution_time_hours' => $resolution_time,
            ]);
        }
    }
}
