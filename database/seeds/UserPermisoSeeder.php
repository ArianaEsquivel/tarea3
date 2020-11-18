<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\user_permiso;
use App\User;
use App\permisos;

class UserPermisoSeeder extends Seeder
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
        $admin = User::select('id')->where('email', '=', 'admin@gmail.com')
        ->first()
        ->pluck('id')
        ->toArray();
        $id_admi = $admin[0];

        for($i = 0; $i < count($permisos); $i++)
        {
            $userpermiso = DB::table('user_permisos')
                ->join('users', 'user_permisos.user_id', '=', 'users.id')
                ->join('permisos', 'user_permisos.permiso_id', '=', 'permisos.id')
                ->select('permisos.id')
                ->where('permisos.tipo', '=', $permisos[$i])
                ->where('users.id', '=', $id_admi)
                ->first();
                //Log::info('userpermiso',[$userpermiso]);
                //Log::info('permisos', [$permisos[$i]]);
            if (!$userpermiso)
            {
                $permiso = permisos::select('id')->where('tipo', '=', $permisos[$i])->get()
                ->pluck('id')
                ->toArray();
                $id_permi = $permiso[0];
                //Log::info('id_permiso', [$id_permi]);
                user_permiso::create([
                    'user_id'=> $id_admi,
                    'permiso_id' => $id_permi]);
            }
        }
    }
}
