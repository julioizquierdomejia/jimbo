<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class users extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();

        $user = new User;
        $user->name = 'Administrador';
        $user->email        = 'admin@gmail.com';
        $user->phone        = '998913140';
        $user->dni          = '06813928';
        $user->password     = bcrypt('998913140');
        $user->role         = 10;
        $user->bio          = $faker->realText();

        $user->save();
        
        
    }
}
