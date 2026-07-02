<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Priority;

class PrioritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $priorities = [
            ['name' => 'Low', 'color' => 'gray'],
            ['name' => 'Medium', 'color' => 'info'],
            ['name' => 'High', 'color' => 'warning'],
            ['name' => 'Critical', 'color' => 'danger'],
        ];

        foreach ($priorities as $priority) {
            Priority::create($priority);
        }
    }
}
