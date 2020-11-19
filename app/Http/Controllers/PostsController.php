<?php

namespace App\Http\Controllers;

use App\posts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;

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
                ->select('posts.id as post_id', 'posts.titulo', 'posts.descripcion','posts.user_id', 'posts.imagen', 'users.name as autor')
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
            if ($request->hasFile('imagen')) {
                switch($request->imagen->extension()){
                    case "jpeg":
                        $path = Storage::disk('public')->putFile('imagenes_posts', $request->imagen);
                    break;
                    case "jpg":
                        $path = Storage::disk('public')->putFile('imagenes_posts', $request->imagen);
                    break;
                    case "heic":
                        $path = Storage::disk('public')->putFile('imagenes_posts', $request->imagen);
                    break;
                    case "png":
                        $path = Storage::disk('public')->putFile('imagenes_posts', $request->imagen);
                    break;
                    default:
                        return response()->json(["Sólo los archivos .jpeg, .jpg, .png y .heic son compatibles, verifica la extensión."], 400);
                    break;
                }
                $post->imagen       = $path;
            }
            if ($post->save()) {
                $guardado = DB::table('posts')
                ->join('users', 'posts.user_id', '=', 'users.id')
                ->select('posts.id', 'posts.titulo', 'posts.descripcion', 'posts.imagen', 'users.name as autor')
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
                if ($eliminado->imagen)
                {
                    Storage::delete('public/'.$eliminado->imagen);
                }
                return response()->json(["Se eliminó el post:"=>$eliminado]);
            }
            else {
                return response()->json("No se eliminó ningún post, verifica que el post exista");
            }
        }
        else if ($request->user()->tokenCan('user:delete')) {
            $eliminado = posts::where('id', $request->id)->where('user_id', $request->user()->id)->first();
            Log::info($eliminado);
            if ($eliminado) {
                if ($eliminado->imagen)
                {
                    Storage::delete('public/'.$eliminado->imagen);
                }
                DB::table('comentarios')->where('post_id', '=', $request->id)->delete();
                DB::table('posts')->where('id', '=', $request->id)->delete();
                return response()->json(["Eliminaste tu post:"=>$eliminado]);
            }
            else {
                return response()->json("No se eliminó ningún post, verifica que el post exista y sea tuyo");
            }
        }
    }

    public function borrarimagen(Request $request)
    {
        if ($request->user()->tokenCan('admin:delete')) {
            $eliminado = posts::select('id', 'titulo', 'imagen')->where('id', $request->post_id)->where('user_id', $request->user_id)->first();
            if ($eliminado->imagen) {
                Storage::delete('public/'.$eliminado->imagen);
                DB::table('users')->where('id', $request->user_id)
                            ->update(['foto' => null]);
                return response()->json(["Se eliminó la imagen del post:"=>$eliminado]);
            }
            else {
                return response()->json("No se eliminó ningúna imagen, verifica que el usuario exista o tenga foto");
            }
        }
        else if ($request->user()->tokenCan('user:delete')) {
            $eliminado = posts::select('id', 'titulo', 'imagen')->where('id', $request->post_id)->where('user_id', $request->user()->id)->first();
            if ($eliminado->imagen) {
                Storage::delete('public/'.$eliminado->imagen);
                DB::table('posts')->where('id', $request->post_id)
                            ->update(['imagen' => null]);
                return response()->json(["Se eliminó la imagen del post:"=>$eliminado]);
            }
            else {
                return response()->json("No se eliminó ningúna imagen, verifica que el post exista o tenga imagen");
            }
        }
    }

    public function cambiarimagen(Request $request)
    {
        if ($request->user()->tokenCan('user:update') or $request->user()->tokenCan('admin:update')) {
            $antes = posts::select('id', 'titulo', 'imagen')->where('id', $request->id)->where('user_id', $request->user()->id)->first();
            if (!$antes)
            {
                return abort(400, "Verifica que el post exista y sea tuyo");
            }
            if ($request->hasFile('imagen')) {
                switch($request->imagen->extension()){
                    case "jpeg":
                        $path = Storage::disk('public')->putFile('imagenes_posts', $request->imagen);
                    break;
                    case "jpg":
                        $path = Storage::disk('public')->putFile('imagenes_posts', $request->imagen);
                    break;
                    case "heic":
                        $path = Storage::disk('public')->putFile('imagenes_posts', $request->imagen);
                    break;
                    case "png":
                        $path = Storage::disk('public')->putFile('imagenes_posts', $request->imagen);
                    break;
                    default:
                        return response()->json(["Sólo los archivos .jpeg, .jpg, .png y .heic son compatibles, verifica la extensión."], 400);
                    break;
                }
                if ($path) {
                    Storage::delete('public/'.$antes->imagen);
                    DB::table('posts')->where('id', $request->id)->update(['imagen' => $path]);
                    $despues = posts::select('id', 'titulo', 'imagen')->where('id', $request->id)->where('user_id', $request->user()->id)->first();
                }
            }
            if ($despues) {
                return response()->json(["Se editó tu imagen del post de:"=>$antes,"a:"=>$despues, 201]);
            }
            return abort(400, "Error al editar tu imagen");
        }
        return abort(401, "No tienes autorización para cambiar imagenes");
    }
}
