<?php

namespace App\Http\Controllers;

use App\comentarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComentariosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->user()->tokenCan('user:index') or $request->user()->tokenCan('admin:index')) {
            $comentarios = DB::table('comentarios')
                ->join('users', 'comentarios.user_id', '=', 'users.id')
                ->join('posts', 'posts.id', '=', 'comentarios.post_id')
                ->select('comentarios.id as comentario_id', 'comentarios.comentario', 'users.name as comentario_autor',
                 'comentarios.post_id', 'posts.titulo as post_titulo')
                ->get();
            return response()->json(["Comentarios:"=>$comentarios], 200);
        }
        return abort(401, "No estás autorizado para ver los comentarios");
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
            'comentario' => 'required',
            'post_id' => 'required',
        ]);
        if ($request->user()->tokenCan('user:create') or $request->user()->tokenCan('admin:create')) {
            $buscar = DB::table('posts')->where('id', $request->post_id)->first();
            if ($buscar) {
                $comentario                = new Comentarios();
                $comentario->comentario    = $request->comentario;
                $comentario->post_id       = $request->post_id;
                $comentario->user_id       = $request->user()->id;
                $comentario->save();
                $guardado = DB::table('comentarios')
                ->join('users', 'comentarios.user_id', '=', 'users.id')
                ->join('posts', 'posts.id', '=', 'comentarios.post_id')
                ->select('comentarios.id as comentario_id', 'comentarios.comentario', 'users.name as comentario_autor',
                 'comentarios.post_id', 'posts.titulo as post_titulo')
                ->where('comentarios.id',$comentario->id)
                ->get();
                return response()->json(["Comentario publicado:"=>$guardado], 201);
            }
            return abort(400, "Verifica que el post que quieres comentar exista");
        }
        return abort(401, "No tienes autorización para comentar posts");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\comentarios  $comentarios
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {
        //$comentario = comentarios::where('id', $id)->first();
        //return $comentario;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\comentarios  $comentarios
     * @return \Illuminate\Http\Response
     */
    public function edit(comentarios $comentarios)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\comentarios  $comentarios
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        /*if ($request->user()->tokenCan('admin:update')) {
            $antes = comentarios::where('id', $request->id)->first();
            DB::table('comentarios') ->where('id', $request->id)
                            ->update(['comentario' => $request->comentario]);
            $despues = comentarios::where('id', $request->id)->first();
            if ($despues) {

                return response()->json(["Se editó el post de:"=>$antes,"a:"=>$despues ]);
            }
            return abort(400, "Error al editar comentario verifique haber llenado los 
            campos y que sean correctos");
        }*/
        if ($request->user()->tokenCan('user:update') or $request->user()->tokenCan('admin:update')) {
            $antes = comentarios::where('id', $request->id)->where('user_id', $request->user()->id)->first();
            if ($antes) {
                DB::table('comentarios') ->where('id', $request->id)
                                        ->update(['comentario' => $request->comentario]);
                $despues = comentarios::where('id', $request->id)->first();
                if ($despues) {
                    return response()->json(["Editaste tu comentario de:"=>$antes,"a:"=>$despues ]);
                }
            }
            return abort(400, "Error al editar comentario seleccione un comentario suyo");
        }
        return abort(401, "No tienes autorización para editar comentarios");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\comentarios  $comentarios
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if ($request->user()->tokenCan('admin:delete')) {
            $eliminado = comentarios::where('id', $request->id)->first();
            DB::table('comentarios')->where('id', '=', $request->id)->delete();
            if ($eliminado) {
                return response()->json(["Se eliminó el comentario:"=>$eliminado]);
            }
            else {
                return response()->json("No se eliminó ningún comentario, verifica que el comentario exista");
            }
        }
        else if ($request->user()->tokenCan('user:delete')) {
            $eliminado = DB::table('comentarios')
                ->join('users', 'comentarios.user_id', '=', 'users.id')
                ->join('posts', 'posts.id', '=', 'comentarios.post_id')
                ->select('comentarios.*')
                ->where('comentarios.id', $request->id)->where('comentarios.user_id', $request->user()->id)
                ->first();
            if ($eliminado) {
                DB::table('comentarios')->where('id', '=', $request->id)->delete();
                return response()->json(["Eliminaste tu comentario:"=>$eliminado]);
            }
            else {
                return response()->json("No se eliminó ningún comentario, verifica que el comentario exista y sea tuyo");
            }
        }
        DB::table('comentarios')->where('id', '=', $id)->delete();
    }
}
