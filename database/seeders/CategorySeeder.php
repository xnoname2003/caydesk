<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Technical Support', 'description' => 'Technical issues with the system or application'],
            ['name' => 'Account Issue', 'description' => 'Problems with login, password, or account access'],
            ['name' => 'Billing', 'description' => 'Questions about billing and payments'],
            ['name' => 'Feature Request', 'description' => 'Suggestions and requests for new features'],
            ['name' => 'Bug Report', 'description' => 'Reports of system errors or bugs'],
            ['name' => 'Infrastructure', 'description' => 'Issues related to servers, networks, or infrastructure'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
