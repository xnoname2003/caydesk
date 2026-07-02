<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use \App\Models\Team;
use Spatie\Permission\PermissionRegistrar;

class UserSeeder extends Seeder
{
    public function run(): void
    {   
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $roleAdmin      = Role::create(['name' => 'administrator', 'guard_name' => 'web']);
        $roleSupervisor = Role::create(['name' => 'supervisor', 'guard_name' => 'web']);
        $roleAgent      = Role::create(['name' => 'agent', 'guard_name' => 'web']);
        $roleCustomer   = Role::create(['name' => 'customer', 'guard_name' => 'web']);

        $techTeam = Team::first();

        $password = Hash::make('password');

        $admin = User::create([
            'name' => 'Moonlight Sonata',
            'email' => 'admin@admin.com',
            'password' => $password,
            'email_verified_at' => now(),
        ]);
        $admin->assignRole($roleAdmin);

        $supervisor = User::create([
            'name' => 'Bu Supervisor',
            'email' => 'supervisor@admin.com',
            'password' => $password,
            'email_verified_at' => now(),
            'team_id' => $techTeam->id,
        ]);
        $supervisor->assignRole($roleSupervisor);

        $agent = User::create([
            'name' => 'Mas Agent',
            'email' => 'agent@admin.com',
            'password' => $password,
            'email_verified_at' => now(),
            'team_id' => $techTeam->id,
        ]);
        $agent->assignRole($roleAgent);

        $customer = User::create([
            'name' => 'Kang Customer',
            'email' => 'customer@demo.com',
            'password' => $password,
            'email_verified_at' => now(),
        ]);
        $customer->assignRole($roleCustomer);
    }
}
