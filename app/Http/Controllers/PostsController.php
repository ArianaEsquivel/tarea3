<?php

namespace App\Http\Controllers;

use App\posts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->user()->tokenCan('user:index') or $request->user()->tokenCan('admin:index')) {
            $posts = DB::table('posts')
                ->join('users', 'posts.user_id', '=', 'users.id')
                ->select('posts.id as post_id', 'posts.titulo', 'posts.descripcion','posts.user_id', 'users.name as autor')
                ->get();
            return response()->json(["Posts:"=>$posts], 200);
        }
        return abort(401, "No estás autorizado para ver los posts");
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
            'titulo' => 'required',
            'descripcion' => 'required',
        ]);
        if ($request->user()->tokenCan('user:create') or $request->user()->tokenCan('admin:create')) {
            $post                = new Posts();
            $post->titulo        = $request->titulo;
            $post->descripcion   = $request->descripcion;
            $post->user_id       = $request->user()->id;
            if ($post->save()) {
                $guardado = DB::table('posts')
                ->join('users', 'posts.user_id', '=', 'users.id')
                ->select('posts.id', 'posts.titulo', 'posts.descripcion','users.name as autor')
                ->where('posts.id',$post->id)
                ->get();
                return response()->json(["Post publicado:"=>$guardado], 201);
            }
            return abort(400, "Error al publicar post");
        }
        return abort(401, "No tienes autorización para publicar posts");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\posts  $posts
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {
        //$post = posts::where('id', $id)->first();
        //return $post;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\posts  $posts
     * @return \Illuminate\Http\Response
     */
    public function edit(posts $posts)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\posts  $posts
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        /*if ($request->user()->tokenCan('admin:update')) {
            $antes = posts::where('id', $request->id)->first();
            DB::table('posts') ->where('id', $request->id)
                            ->update(['titulo' => $request->titulo, 
                            'descripcion' => $request->descripcion]);
            $despues = posts::where('id', $request->id)->first();
            if ($despues) {
                return response()->json(["Se editó el post de:"=>$antes,"a:"=>$despues ]);
            }
            return abort(400, "Error al editar post verifique haber llenado los 
            campos y que sean correctos");
        }*/
        if ($request->user()->tokenCan('user:update') or $request->user()->tokenCan('admin:update') ) {
            $antes = posts::where('id', $request->id)->where('user_id', $request->user()->id)->first();
            if ($antes) {
                DB::table('posts') ->where('id', $request->id)
                                    ->update(['titulo' => $request->titulo, 
                                    'descripcion' => $request->descripcion]);
                $despues = posts::where('id', $request->id)->first();
                if ($despues) {
                    return response()->json(["Editaste tu post de:"=>$antes,"a:"=>$despues ]);
                }
            }
            return abort(400, "Error al editar post seleccione un post suyo");
        }
        return abort(401, "No tienes autorización para editar posts");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\posts  $posts
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if ($request->user()->tokenCan('admin:delete')) {
            $eliminado = posts::where('id', $request->id)->first();
            DB::table('comentarios')->where('post_id', '=', $request->id)->delete();
            DB::table('posts')->where('id', '=', $request->id)->delete();
            if ($eliminado) {
                return response()->json(["Se eliminó el post:"=>$eliminado]);
            }
            else {
                return response()->json("No se eliminó ningún post, verifica que el post exista");
            }
        }
        else if ($request->user()->tokenCan('user:delete')) {
            $eliminado = posts::where('id', $request->id)->where('user_id', $request->user()->id)->first();
            if ($eliminado) {
                DB::table('comentarios')->where('post_id', '=', $request->id)->delete();
                DB::table('posts')->where('id', '=', $request->id)->delete();
                return response()->json(["Eliminaste tu post:"=>$eliminado]);
            }
            else {
                return response()->json("No se eliminó ningún post, verifica que el post exista y sea tuyo");
            }
        }
    }
}
