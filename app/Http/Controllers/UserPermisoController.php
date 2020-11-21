<?php

namespace App\Http\Controllers;

use App\user_permiso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Log;
use App\permisos;
use App\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Mailable;

class UserPermisoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->tokenCan('admin:index')) {
            $user_permisos = DB::table('user_permisos')
            ->join('users', 'user_permisos.user_id', '=', 'users.id')
            ->join('permisos', 'user_permisos.permiso_id', '=', 'permisos.id')
            ->select('user_permisos.id', 'users.id as user_id', 'users.name', 'permisos.id as permiso_id', 'permisos.tipo')
            ->get();
            return $user_permisos;
        }
        else {
            $data = array (
                'name' => $user->name, 
                'email' => $user->email, 
                'permiso' => 'admin:index',
                'razón' => 'ver la lista de permisos de usuarios'
            );
            $buscarAdmins = DB::table('user_permisos')
            ->join('users', 'user_permisos.user_id', '=', 'users.id')
            ->join('permisos', 'user_permisos.permiso_id', '=', 'permisos.id')
            ->select('users.name', 'users.email')
            ->where('permisos.tipo', 'admin:asignar')
            ->get();
            foreach($buscarAdmins as $admins=>$admin)
            {
                Mail::send('emails.sinpermiso', $data, function ($message) use ($data, $admin) {
                    $message->from('19170089@uttcampus.edu.mx', 'Api práctica 3');
                    $message->to($admin->email, $admin->name)->
                    subject('Aviso');
                });
            }
            return abort(401, "No tienes permiso de ver los permisos de los usuarios");
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if ($user->tokenCan('admin:asignar')) {
            $request->validate([
                'user_id' => 'required',
                'permiso_id' => 'required',
            ]);
            $permiso = permisos::where('id', $request->permiso_id)->first();
            $user = User::where('id', '=', $request->user_id)->first();
            if (!$permiso || !$user)
            {
                return abort(400, "Verifica que tu user_id y permiso_id sean existentes");
            }
            $buscar = user_permiso::where('permiso_id', $request->permiso_id)->where('user_id', '=', $request->user_id)->first();
            Log::info($buscar);
            if (!$buscar) {
                $user_permiso                 = new User_Permiso();
                $user_permiso->user_id        = $request->user_id;
                $user_permiso->permiso_id     = $request->permiso_id;
                $hecho = $user_permiso->save();
                $insertado = DB::table('user_permisos')
                ->join('users', 'user_permisos.user_id', '=', 'users.id')
                ->join('permisos', 'user_permisos.permiso_id', '=', 'permisos.id')
                ->select('user_permisos.id', 'users.name', 'permisos.tipo')
                ->where('user_permisos.id', '=', $user_permiso->id )
                ->get();
                
                if ($hecho) {
                    return response()->json($insertado, 201);
                }
                return abort(400, "Error al asignar permiso");
            }
            return abort(201, "Este permiso ya ha sido asignado");
        }
        else {
            $data = array (
                'name' => $user->name, 
                'email' => $user->email, 
                'permiso' => 'admin:asignar',
                'razón' => 'asignar un permiso'
            );
            $buscarAdmins = DB::table('user_permisos')
                    ->join('users', 'user_permisos.user_id', '=', 'users.id')
                    ->join('permisos', 'user_permisos.permiso_id', '=', 'permisos.id')
                    ->select('users.name', 'users.email')
                    ->where('permisos.tipo', 'admin:asignar')
                    ->get();
                foreach($buscarAdmins as $admins=>$admin)
                {
                    Mail::send('emails.sinpermiso', $data, function ($message) use ($data, $admin) {
                        $message->from('19170089@uttcampus.edu.mx', 'Api práctica 3');
                        $message->to($admin->email, $admin->name)->
                        subject('Aviso');
                    });
                }
            return abort(401, "No tienes permiso de asignar permisos");
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\user_permiso  $user_permiso
     * @return \Illuminate\Http\Response
     */
    public function show(user_permiso $user_permiso)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\user_permiso  $user_permiso
     * @return \Illuminate\Http\Response
     */
    public function edit(user_permiso $user_permiso)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\user_permiso  $user_permiso
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, user_permiso $user_permiso)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\user_permiso  $user_permiso
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $user = $request->user();
        if ($user->tokenCan('admin:delete')) {
            $eliminado = user_permiso::where('permiso_id', $request->permiso_id)->where('user_id', '=', $request->user_id)->get();
            $hecho = user_permiso::where('permiso_id', $request->permiso_id)->where('user_id', '=', $request->user_id)->delete();
            if ($hecho) {
                return response()->json(["Se desvinculó el permiso:"=>$eliminado]);
            }
            else {
                return response()->json("No se desvinculó ningún permiso, verifica que el el permiso esté asignado al usuario");
            }
        }
        else {
            $data = array (
                'name' => $user->name, 
                'email' => $user->email, 
                'permiso' => 'admin:delete',
                'razón' => 'desvincular un permiso'
            );
            $buscarAdmins = DB::table('user_permisos')
            ->join('users', 'user_permisos.user_id', '=', 'users.id')
            ->join('permisos', 'user_permisos.permiso_id', '=', 'permisos.id')
            ->select('users.name', 'users.email')
            ->where('permisos.tipo', 'admin:asignar')
            ->get();
            foreach($buscarAdmins as $admins=>$admin)
            {
                Mail::send('emails.sinpermiso', $data, function ($message) use ($data, $admin) {
                    $message->from('19170089@uttcampus.edu.mx', 'Api práctica 3');
                    $message->to($admin->email, $admin->name)->
                    subject('Aviso');
                });
            }
            return response()->json("No tienes permiso de desvincular permisos", 401);
        }
    }
    public function tipospermisos(Request $request)
    {
        $user = $request->user();
        if ($user->tokenCan('admin:asignar')) {
            $permisos = DB::table('permisos')->select('id')->where('tipo', 'like', $request->rol.':%')->get()->pluck('id')
            ->toArray();
            $user = User::where('id', '=', $request->user_id)->first();
            //Log::info(["permisos"=>$permisos]);
            //Log::info("user".$user);
            if (!$permisos || !$user)
            {
                return abort(400, "Verifica que tu user_id y rol sean existentes");
            }
            $tot = count($permisos);
            $hubo = 0;
            for($i = 0; $i < $tot; $i++)
            {
                //Log::info("IDD".$permisos[$i]);
                $userpermiso = DB::table('user_permisos')
                ->join('users', 'user_permisos.user_id', '=', 'users.id')
                ->join('permisos', 'user_permisos.permiso_id', '=', 'permisos.id')
                ->select('permisos.id')
                ->where('permisos.id', '=', $permisos[$i])
                ->where('users.id', '=', $request->user_id)
                ->first();
                
                //Log::info('userpermiso',[$userpermiso]);
                if (!$userpermiso)
                {
                    $hubo++;
                    $id_permi = $permisos[$i];
                    //Log::info("id_permi".$id_permi);
                    $user_permiso                 = new User_Permiso();
                    $user_permiso->user_id        = $request->user_id;
                    $user_permiso->permiso_id     = $id_permi;
                    $hecho = $user_permiso->save();
                }
                //user_permiso::insert([
                    //  'user_id'=> $request->user_id,
                    //'permiso_id' => $permisos[$i]]);
            }
            if ($hubo == 0)
            {
                return response()->json("Estos roles ya están asignados al usuario", 201);
            }
            else
            {
                return response()->json("Fueron asignados " . $hubo. " roles al user_id " . $request->user_id, 201);
            }
            //Log::info("countt ".count($permisos));
        }
        else {
            $data = array (
                'name' => $user->name, 
                'email' => $user->email, 
                'permiso' => 'admin:asignar',
                'razón' => 'asignar un conjunto de permisos'
            );
            $buscarAdmins = DB::table('user_permisos')
            ->join('users', 'user_permisos.user_id', '=', 'users.id')
            ->join('permisos', 'user_permisos.permiso_id', '=', 'permisos.id')
            ->select('users.name', 'users.email')
            ->where('permisos.tipo', 'admin:asignar')
            ->get();
            foreach($buscarAdmins as $admins=>$admin)
            {
                Mail::send('emails.sinpermiso', $data, function ($message) use ($data, $admin) {
                    $message->from('19170089@uttcampus.edu.mx', 'Api práctica 3');
                    $message->to($admin->email, $admin->name)->
                    subject('Aviso');
                });
            }
            return response()->json("No tienes permiso de asignar conjuntos de permisos", 401);
        }
    }
}
