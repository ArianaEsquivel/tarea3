<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\user_permiso;

class UserPermisoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        user_permiso::create([
            'user_id'=> 1,
            'permiso_id' => 1]);
        user_permiso::create([
            'user_id'=> 1,
            'permiso_id' => 2]);
        user_permiso::create([
            'user_id'=> 1,
            'permiso_id' => 3]);
        user_permiso::create([
            'user_id'=> 1,
            'permiso_id' => 4]);
        user_permiso::create([
            'user_id'=> 1,
            'permiso_id' => 5]);
    }
}
