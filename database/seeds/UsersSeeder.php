<?php

use Illuminate\Database\Seeder;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::where('email', '19170089@uttcampus.edu.mx')->first();  
        //Log::info($admin);
        if (!$admin)
        {
            User::create([
                'name' => 'Ariana Yamileth',
                'age' => 20,
                'email' => '19170089@uttcampus.edu.mx',
                'password' => Hash::make('123456'),
                'codigo' => Str::random(25),
                'email_verified_at' => Carbon::now()
                ]);
        }
    }
}
