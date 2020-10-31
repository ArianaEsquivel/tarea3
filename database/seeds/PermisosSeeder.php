<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\permisos;

class PermisosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        permisos::create([
            'id' => 1,
            'tipo'=> 'admin:asignar']);
        permisos::create([
            'id' => 2,
            'tipo'=> 'admin:index']);
        permisos::create([
            'id' => 3,
            'tipo'=> 'admin:create']);
        permisos::create([
            'id' => 4,
            'tipo'=> 'admin:update']);
        permisos::create([
            'id' => 5,
            'tipo'=> 'admin:delete']);
        permisos::create([
            'id' => 6,
            'tipo'=> 'user:index']);
        permisos::create([
            'id' => 7,
            'tipo'=> 'user:create']);
        permisos::create([
            'id' => 8,
            'tipo'=> 'user:update']);
        permisos::create([
            'id' => 9,
            'tipo'=> 'user:delete']);
    }
}
