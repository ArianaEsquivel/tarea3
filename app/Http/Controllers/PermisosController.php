<?php

namespace App\Http\Controllers;

use App\permisos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Mailable;

class PermisosController extends Controller
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
            return permisos::all();
        }
        else {
            $data = array (
                'name' => $user->name, 
                'email' => $user->email, 
                'permiso' => 'admin:index',
                'razón' => 'ver la lista de permisos'
            );
            Mail::send('emails.sinpermiso', $data, function ($message) use ($data) {
                $message->from('19170089@uttcampus.edu.mx', 'Ariana Esquivel');
                $message->to('19170089@uttcampus.edu.mx', 'Administrador')->subject('Aviso');
            });
            return abort(401, "No tienes permiso de ver los permisos");
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
        $request->validate([
            'tipo' => 'required',
        ]);
        $user = $request->user();
        if ($user->tokenCan('admin:create')) {
            $permiso              = new Permisos();
            $permiso->tipo        = $request->tipo;
            if ($permiso->save()) {
                return response()->json($permiso, 201);
            }
            return abort(400, "Error al registrar permiso");
        }
        else {
            $data = array (
                'name' => $user->name, 
                'email' => $user->email, 
                'permiso' => 'admin:create',
                'razón' => 'crear un permiso',
                'tipo' => $request->tipo
            );
            Mail::send('emails.sinpermiso', $data, function ($message) use ($data) {
                $message->from('19170089@uttcampus.edu.mx', 'Ariana Esquivel');
                $message->to('19170089@uttcampus.edu.mx', 'Administrador')->
                subject('Intentando crear un "'. $data['tipo'].'"');
            });
            return abort(401, "No tienes permiso de crear permisos");
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\permisos  $permisos
     * @return \Illuminate\Http\Response
     */
    public function show(permisos $permisos)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\permisos  $permisos
     * @return \Illuminate\Http\Response
     */
    public function edit(permisos $permisos)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\permisos  $permisos
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = $request->user();
        if ($user->tokenCan('admin:update')) {
            $affected = DB::table('permisos')
                            ->where('id', $request->id)
                            ->update(['tipo' => $request->tipo]);
            if ($affected) {
                return response()->json("Se editó ".$affected." permiso");
            }
            return abort(400, "Error al editar permiso verifica que tus datos sean correctos");
        }
        else {
            $permiso = permisos::findorFail($request->id);
            $data = array (
                'name' => $user->name, 
                'email' => $user->email, 
                'permiso' => 'admin:update',
                'razón' => 'cambiar un permiso',
                'viejo' => $permiso ->tipo,
                'nuevo' => $request->tipo
            );

            Mail::send('emails.sinpermiso', $data, function ($message) use ($data) {
                $message->from('19170089@uttcampus.edu.mx', 'Ariana Esquivel');
                $message->to('19170089@uttcampus.edu.mx', 'Administrador')->
                subject('Intentando cambiar de "'. $data['viejo']. '" a "'. $data['nuevo'].'"');
            });
            return abort(401, "No tienes permiso de crear permisos");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\permisos  $permisos
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $user = $request->user();
        if ($user->tokenCan('admin:delete')) {
            $eliminado = DB::table('permisos')->where('id', $request->id)->first();
            if ($eliminado) {
                DB::table('user_permisos')->where('permiso_id', $request->id)->where('permiso_id', '=', $request->id)->delete();
                DB::table('permisos')->where('id', '=', $request->id)->delete();
                return response()->json(["Eliminaste el permiso:"=>$eliminado]);
            }
            else {
                return response()->json("No se eliminó ningún permiso verifica tus datos");
            }
        }
        else {
            $permiso = permisos::findorFail($request->id);
            $data = array (
                'name' => $user->name, 
                'email' => $user->email, 
                'permiso' => 'admin:delete',
                'razón' => 'eliminar un permiso',
                'tipo' => $permiso ->tipo
            );

            Mail::send('emails.sinpermiso', $data, function ($message) use ($data) {
                $message->from('19170089@uttcampus.edu.mx', 'Ariana Esquivel');
                $message->to('19170089@uttcampus.edu.mx', 'Administrador')->
                subject('Intentando eliminar el permiso "'. $data['tipo']. '"');
            });
            return response()->json("No tienes permiso de eliminar permisos", 401);
        }
    }
}
