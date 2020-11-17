<?php

namespace App\Http\Controllers;

use App\permisos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermisosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->user()->tokenCan('admin:index')) {
            return permisos::all();
        }
        return abort(401, "No tienes permiso para ver los permisos");
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
        if ($request->user()->tokenCan('admin:create')) {
            $permiso              = new Permisos();
            $permiso->tipo        = $request->tipo;
            if ($permiso->save()) {
                return response()->json($permiso, 201);
            }
            return abort(400, "Error al registrar permiso");
        }
        return abort(401, "No tienes autorización para crear permisos");
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
    public function update(Request $request, int $id)
    {
        if ($request->user()->tokenCan('admin:update')) {
            $affected = DB::table('permisos')
                            ->where('id', $id)
                            ->update(['tipo' => $request->tipo]);
            if ($affected) {
                return response()->json("Se editó ".$affected." permiso");
            }
            return abort(400, "Error al editar permiso");
        }
        return abort(401, "No tienes autorización para editar permisos");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\permisos  $permisos
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if ($request->user()->tokenCan('admin:delete')) {
            $eliminado = DB::table('permisos')->where('id', $request->id)->first();
            if ($eliminado) {
                DB::table('user_permisos')->where('permiso_id', $request->id)->where('permiso_id', '=', $request->id)->delete();
                DB::table('permisos')->where('id', '=', $request->id)->delete();
                return response()->json(["Eliminaste el permiso:"=>$eliminado]);
            }
            else {
                return response()->json("No se eliminó ningún permiso");
            }
        }
        return abort(401, "No tienes autorización para eliminar permisos");
    }
}
