<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator RAM',
                'email' => 'admin@sumberrezeki.com',
                'role' => 'admin',
                'password' => \Illuminate\Support\Facades\Hash::make('Admin@2026'),
            ]
        );

        \App\Models\User::updateOrCreate(
            ['username' => 'owner'],
            [
                'name' => 'Pemilik Usaha (Owner)',
                'email' => 'owner@sumberrezeki.com',
                'role' => 'owner',
                'password' => \Illuminate\Support\Facades\Hash::make('Owner@2026'),
            ]
        );

        $this->call(PurchaseSeeder::class);
    }
}
