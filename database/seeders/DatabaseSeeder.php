<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call([
            CatalogSeeder::class,
            ProductSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        Address::create([
            'id'             => 1,
            'user_id'        => 1, // نفس الـ user_id اللي ظاهر عندك في الـ error
            'address_line_1' => '9 شارع التحرير، الدقي',
            'city'           => 'Cairo',
            'governorate'    => 'Giza',
            'phone'          => '01012345678',
            'is_default'     => true,
        ]);
    }
}
