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
        $permisos = ['admin:asignar', 'admin:index', 'admin:create', 'admin:update', 'admin:delete',
         'user:index', 'user:create', 'user:update', 'user:delete'];

        for($i = 0; $i < count($permisos); $i++)
        {
            $permiso = permisos::where('tipo', '=', $permisos[$i])->first();
            if (!$permiso)
            {
                permisos::create([
                    'tipo'=> $permisos[$i]]);
            }
        }
    }
}
