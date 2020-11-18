<?php

use Illuminate\Database\Seeder;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::where('email', 'admin@gmail.com')->first();  
        //Log::info($admin);
        if (!$admin)
        {
            User::create([
                'name' => 'Administrador',
                'age' => 20,
                'email' => 'admin@gmail.com',
                'password' => Hash::make('123456')]);
        }
    }
}
