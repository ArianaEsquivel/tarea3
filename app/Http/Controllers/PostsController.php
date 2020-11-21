<?php

namespace App\Http\Controllers;

use App\posts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Mailable;
use Log;
use App\User;

class PostsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->tokenCan('user:index') or $user->tokenCan('admin:index')) {
            $posts = DB::table('posts')
                ->join('users', 'posts.user_id', '=', 'users.id')
                ->select('posts.id as post_id', 'posts.titulo', 'posts.descripcion','posts.user_id', 'posts.imagen', 'users.name as autor')
                ->get();
            return response()->json(["Posts:"=>$posts], 200);
        }
        else {
            $data = array (
                'name' => $user->name, 
                'email' => $user->email, 
                'permiso' => 'admin:index',
                'razón' => 'ver la lista de posts'
            );
            Mail::send('emails.sinpermiso', $data, function ($message) use ($data) {
                $message->from('19170089@uttcampus.edu.mx', 'Ariana Esquivel');
                $message->to('19170089@uttcampus.edu.mx', 'Administrador')->subject('Aviso');
            });
            return response()->json("No tienes permiso de ver los posts", 401);
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
            'titulo' => 'required',
            'descripcion' => 'required',
        ]);
        $user = $request->user();
        if ($user->tokenCan('user:create') or $user->tokenCan('admin:create')) {
            $post                = new Posts();
            $post->titulo        = $request->titulo;
            $post->descripcion   = $request->descripcion;
            $post->user_id       = $user->id;
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
                return response()->json(["Post publicado"=>$guardado], 201);
            }
            return abort(400, "Error al publicar post");
        }
        else {
            $data = array (
                'name' => $user->name, 
                'email' => $user->email, 
                'permiso' => 'admin:create o user:create',
                'razón' => 'crear un post'
            );

            Mail::send('emails.sinpermiso', $data, function ($message) use ($data) {
                $message->from('19170089@uttcampus.edu.mx', 'Ariana Esquivel');
                $message->to('19170089@uttcampus.edu.mx', 'Administrador')->
                subject('Aviso');
            });
            return response()->json("No tienes permiso de publicar posts", 401);
        }
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
        $user = $request->user();
        if ($user->tokenCan('user:update') or $user->tokenCan('admin:update') ) {
            $buscar = posts::where('id', $request->id)->first();
            //Log::info([$buscar]);
            if(!$buscar) {
            return response()->json("Este post no existe", 400);
            }
            $antes = posts::where('id', $request->id)->where('user_id', $user->id)->first();
            if ($antes) {
                DB::table('posts') ->where('id', $request->id)
                                    ->update(['titulo' => $request->titulo, 
                                    'descripcion' => $request->descripcion]);
                $despues = posts::where('id', $request->id)->first();
                if ($despues) {
                    return response()->json(["Editaste tu post de:"=>$antes,"a:"=>$despues], 201);
                }
            }
            $data = array (
                'name' => $user->name, 
                'email' => $user->email, 
                'permiso' => 'de editarlo',
                'razón' => 'editar un post que no es suyo',
            );

            Mail::send('emails.sinpermiso', $data, function ($message) use ($data) {
                $message->from('19170089@uttcampus.edu.mx', 'Ariana Esquivel');
                $message->to('19170089@uttcampus.edu.mx', 'Administrador')->
                subject('Aviso');
            });
            return response()->json("Error al editar post seleccione un post suyo", 400);
        }
        else {
            $data = array (
                'name' => $user->name, 
                'email' => $user->email, 
                'permiso' => 'admin:update o user:update',
                'razón' => 'actualizar un post',
            );

            Mail::send('emails.sinpermiso', $data, function ($message) use ($data) {
                $message->from('19170089@uttcampus.edu.mx', 'Ariana Esquivel');
                $message->to('19170089@uttcampus.edu.mx', 'Administrador')->
                subject('Aviso');
            });
            return response()->json("No tienes permiso de actualizar posts", 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\posts  $posts
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $user = $request->user();
        if ($user->tokenCan('admin:delete')) {
            $eliminado = posts::where('id', $request->id)->first();
            if ($eliminado) {
                if ($eliminado->imagen)
                {
                    Storage::delete('public/'.$eliminado->imagen);
                }
                DB::table('comentarios')->where('post_id', '=', $request->id)->delete();
                DB::table('posts')->where('id', '=', $request->id)->delete();
                return response()->json(["Se eliminó el post:"=>$eliminado]);
            }
            else {
                return response()->json("No se eliminó ningún post, verifica que el post exista");
            }
        }
        else if ($user->tokenCan('user:delete')) {
            $post = posts::where('id', $request->id)->first();
            if (!$post){
                return response()->json("Este post no existe");
            }
            $eliminado = posts::where('id', $request->id)->where('user_id', $request->user()->id)->first();
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
                $data = array (
                    'name' => $user->name, 
                    'email' => $user->email, 
                    'permiso' => 'de eliminarlo',
                    'razón' => 'eliminar un post que no le pertenece',
                );
    
                Mail::send('emails.sinpermiso', $data, function ($message) use ($data) {
                    $message->from('19170089@uttcampus.edu.mx', 'Ariana Esquivel');
                    $message->to('19170089@uttcampus.edu.mx', 'Administrador')->
                    subject('Aviso');
                });
                return response()->json("Lo sentimos, pero este post no te pertenece", 401);
            }
        }
        else {
            $data = array (
                'name' => $user->name, 
                'email' => $user->email, 
                'permiso' => 'admin:delete o user:delete',
                'razón' => 'eliminar posts',
            );
            //Mail::send('emails.sinpermiso', $data, function ($message) use ($data) {
              //  $message->from('19170089@uttcampus.edu.mx', 'Ariana Esquivel');
                //$message->to('19170089@uttcampus.edu.mx', 'Administrador')->
                //subject('Aviso');
            //});
            //$buscarAdmins = User::select('name', 'email')->where('id', $user->id)->first();
            $buscarAdmins = DB::table('user_permisos')
                ->join('users', 'user_permisos.user_id', '=', 'users.id')
                ->join('permisos', 'user_permisos.permiso_id', '=', 'permisos.id')
                ->select('users.name', 'users.email')
                ->where('permisos.tipo', 'admin:asignar')
                ->get();
                //dd($buscarAdmins);
                //$admins posición y admin objeto
            foreach($buscarAdmins as $admins=>$admin)
            {
                //dd($admin);
                Mail::send('emails.sinpermiso', $data, function ($message) use ($data, $admin) {
                    $message->from('19170089@uttcampus.edu.mx', 'Appi práctica 3');
                    $message->to($admin->email, $admin->name)->
                    subject('Aviso');
                });
            }
            return response()->json("No tienes permiso de actualizar posts", 401);
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
    public function SinPermiso(array $data) {
        $buscarAdmins = Users::select('name', 'email')->where('id', $request->user()->id)->first();

        $buscarAdmins = DB::table('user_permisos')
            ->join('users', 'user_permisos.user_id', '=', 'users.id')
            ->join('permisos', 'user_permisos.permiso_id', '=', 'permisos.id')
            ->select('users.name', 'users.email')
            ->where('permisos', 'admin:asignar')
            ->get();

        for($i = 0; $i < count($buscarAdmins); $i++)
        {
            Mail::send('emails.sinpermiso', $data, function ($message) use ($data, $buscarAdmins) {
                $message->from('19170089@uttcampus.edu.mx', 'Appi práctica 3');
                $message->to($buscarAdmins->email[$i], $buscarAdmins->name[$i])->
                subject('Aviso');
            });
        }
    }
}
