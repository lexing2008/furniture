<?php

namespace Database\Seeders;

use App\Enums\Roles;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * php artisan db:seed --class=UserAdminSeeder
 */
class UserAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Добавление одного конкретного пользователя
        User::create([
            'name' => 'Иван Иванов',
            'email' => 'admin@furniture.com',
            'password' => Hash::make('12345678'), // Обязательно хешируем пароль
            'email_verified_at' => now(),
            'role' => Roles::ADMIN,
        ]);

        // Альтернатива: создание множества случайных пользователей через фабрику (если она настроена)
        // User::factory(10)->create();
    }
}
