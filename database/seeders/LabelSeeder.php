<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Label;

class LabelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $labels = [
            ['name' => 'Urgent', 'color' => 'danger'],
            ['name' => 'Backend', 'color' => 'info'],
            ['name' => 'Frontend', 'color' => 'info'],
            ['name' => 'Database', 'color' => 'warning'],
            ['name' => 'Security', 'color' => 'danger'],
            ['name' => 'Needs Follow Up', 'color' => 'warning'],
            ['name' => 'Customer Waiting', 'color' => 'success'],
        ];

        foreach ($labels as $label) {
            Label::create($label);
        }
    }
}
