<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Models\UserModel;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = model(UserModel::class);

        // Create Developer user
        $user = $users->where('email', 'developer@mail.io')->first();

        if (! $user) {
            $user = $users->newUser([
                'email'    => 'developer@mail.io',
                'username' => 'developer',
                'password' => '123456', // Shield will hash it
            ]);

            $users->save($user);
        }

        // Add to a superadmin group
        $user = $users->findById($user->id ?? $user['id']);

        $user->addGroup('superadmin');
    }
}
