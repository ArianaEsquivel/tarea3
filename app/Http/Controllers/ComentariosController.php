<?php

namespace App\Http\Controllers;

use App\comentarios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Mailable;
use App\User;
use Log;
use Illuminate\Support\Facades\Storage;

class ComentariosController extends Controller
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
            $comentarios = DB::table('comentarios')
                ->join('users', 'comentarios.user_id', '=', 'users.id')
                ->join('posts', 'posts.id', '=', 'comentarios.post_id')
                ->select('comentarios.post_id', 'posts.titulo as post_titulo',
                'comentarios.id as comentario_id', 'comentarios.comentario', 'users.name as comentario_autor')
                ->get();
            return response()->json(["Comentarios:"=>$comentarios], 200);
        }
        else {
            $data = array (
                'name' => $user->name, 
                'email' => $user->email, 
                'permiso' => 'admin:index',
                'razón' => 'ver la lista de comentarios'
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
                    $message->to($admin->email, $admin->name)
                    ->subject('Aviso');
                });
            }
            return response()->json("No tienes permiso de ver los comentarios", 401);
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
            'comentario' => 'required',
            'post_id' => 'required',
        ]);
        $user = $request->user();
        if ($user->tokenCan('user:create') or $user->tokenCan('admin:create')) {
            $buscar = DB::table('posts')->where('id', $request->post_id)->first();
            if ($buscar) {
                $comentario                = new Comentarios();
                $comentario->comentario    = $request->comentario;
                $comentario->post_id       = $request->post_id;
                $comentario->user_id       = $user->id;
                $comentario->save();
                $guardado = DB::table('comentarios')
                ->join('posts', 'posts.id', '=', 'comentarios.post_id')
                ->join('users', 'posts.user_id', '=', 'users.id')
                ->select('comentarios.post_id', 'posts.titulo as post_titulo', 'posts.imagen', 'users.name as post_autor',
                    'comentarios.id as comentario_id', 'comentarios.comentario')
                ->where('comentarios.id',$comentario->id)
                ->first();
                $receptor =  DB::table('posts')
                ->join('users', 'posts.user_id', '=', 'users.id')
                ->select('users.email', 'posts.descripcion')
                ->where('posts.id',$guardado->post_id)
                ->first();
                $data = array (
                    'comentario_autor' => $user->name, 
                    'comentario_email' => $user->email,
                    'comentario_comentario' => $guardado->comentario,
                    'post_autor' => $guardado->post_autor, 
                    'post_email' => $receptor->email,
                    'post_titulo' => $guardado->post_titulo,
                    'post_descripcion' => $receptor->descripcion,
                    'post_imagen' => $guardado->imagen
                );
                Mail::send('emails.comentariocreado', $data, function ($message) use ($data) {
                    $message->from('19170089@uttcampus.edu.mx', 'Ariana Esquivel');
                    $message->to($data['comentario_email'], $data['comentario_autor'])->
                    subject('Aviso');
                });
                Mail::send('emails.comentariorecibido', $data, function ($message) use ($data) {
                    $message->from('19170089@uttcampus.edu.mx', 'Ariana Esquivel');
                    $message->to($data['post_email'], $data['post_autor'])->
                    subject('Aviso');
                });
                return response()->json(["Comentario publicado:"=>$guardado], 201);
            }
            return abort(400, "Verifica que el post que quieres comentar exista");
        }
        else {
            $data = array (
                'name' => $user->name, 
                'email' => $user->email, 
                'permiso' => 'admin:create o user:create',
                'razón' => 'comentar un post'
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
                    $message->to($admin->email, $admin->name)
                    ->subject('Aviso');
                });
            }
            return response()->json("No tienes permiso de comentar posts", 401);
        }
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
        $user = $request->user();
        if ($user->tokenCan('user:update') or $user->tokenCan('admin:update')) {
            $buscar = comentarios::where('id', $request->id)->first();
            if(!$buscar) {
                return response()->json("Este comentario no existe", 400);
            }
            $antes = comentarios::where('id', $request->id)->where('user_id', $user->id)->first();
            if ($antes) {
                DB::table('comentarios') ->where('id', $request->id)
                                        ->update(['comentario' => $request->comentario]);
                $despues = comentarios::where('id', $request->id)->first();
                if ($despues) {
                    return response()->json(["Editaste tu comentario de:"=>$antes,"a:"=>$despues ]);
                }
            }
            $data = array (
                'name' => $user->name, 
                'email' => $user->email, 
                'permiso' => 'de editarlo',
                'razón' => 'editar un comentario que no es suyo',
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
                    $message->to($admin->email, $admin->name)
                    ->subject('Aviso');
                });
            }
            return response()->json("Error al editar post seleccione un comentario suyo", 400);
        }
        else {
            $data = array (
                'name' => $user->name, 
                'email' => $user->email, 
                'permiso' => 'admin:update o user:update',
                'razón' => 'actualizar un comentario',
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
                    $message->to($admin->email, $admin->name)
                    ->subject('Aviso');
                });
            }
            return response()->json("No tienes permiso de actualizar comentarios", 401);
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
        $user = $request->user();
        if ($user->tokenCan('admin:delete')) {
            $eliminado = comentarios::where('id', $request->id)->first();
            DB::table('comentarios')->where('id', '=', $request->id)->delete();
            if ($eliminado) {
                return response()->json(["Se eliminó el comentario:"=>$eliminado]);
            }
            else {
                return response()->json("No se eliminó ningún comentario, verifica que el comentario exista");
            }
        }
        else if ($user->tokenCan('user:delete')) {
            $post = comentarios::where('id', $request->id)->first();
            if (!$post){
                return response()->json("Este comentario no existe");
            }
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
                $data = array (
                    'name' => $user->name, 
                    'email' => $user->email, 
                    'permiso' => 'de eliminarlo',
                    'razón' => 'eliminar un comentario que no le pertenece',
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
                        $message->to($admin->email, $admin->name)
                        ->subject('Aviso');
                    });
                }
                return response()->json("Lo sentimos, pero este post no te pertenece", 401);
            }
        }
        else {
            $data = array (
                'name' => $user->name, 
                'email' => $user->email, 
                'permiso' => 'admin:delete o user:delete',
                'razón' => 'eliminar comentario',
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
                    $message->to($admin->email, $admin->name)
                    ->subject('Aviso');
                });
            }
            return response()->json("No tienes permiso de eliminar este comentario", 401);
        }
    }
}
