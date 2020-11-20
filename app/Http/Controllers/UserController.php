<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Mailable;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\user_permiso;
use Log;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->user()->tokenCan('user:index')) {
            return response()->json(["Mi perfil"=>$request->user()], 200);
        }
        else if ($request->user()->tokenCan('admin:index')) {
            return response()->json(["Usuarios registrados"=>user::all()], 200);
        }
        return abort(401, "No estás autorizado para ver usuarios");
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
        //$users = User::create($request->all());
        //return $users;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //$users = User::where('id', $id)->first();
        //return $users;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        if ($request->user()->tokenCan('admin:update')) {
            $antes = user::where('id', $request->id)->first();
            DB::table('users') ->where('id', $request->id)
                            ->update(['name' => $request->name, 
                            'age' => $request->age, 
                            'email' => $request->email,
                            'password' => Hash::make($request->password)]);
            $despues = user::where('id', $request->id)->first();
            if ($despues) {
                return response()->json(["Se editó el usuario de:"=>$antes,"a:"=>$despues ]);
            }
            return abort(400, "Error al editar usuario, verifique haber llenado todos los 
            campos incluyendo el id y que este pertenezca a un usuario");
        }
        else if ($request->user()->tokenCan('user:update')) {
            $antes = user::where('id', $request->user()->id)->first();
            DB::table('users') ->where('id', $request->user()->id)
                            ->update(['name' => $request->name, 
                            'age' => $request->age, 
                            'email' => $request->email,
                            'password' => Hash::make($request->password)]);
            $despues = user::where('id', $request->user()->id)->first();
            if ($despues) {
                return response()->json(["Editaste tu perfil de:"=>$antes,"a:"=>$despues ]);
            }
            return abort(400, "Error al editar usuario, verifique haber llenado todos los 
            campos");
        }
        return abort(401, "No tienes autorización para editar usuarios");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if ($request->user()->tokenCan('admin:delete')) {
            $eliminado = User::where('id', '=', $request->id)->first();
            if ($eliminado) {
                $eliminado->tokens()->delete();
                DB::table('user_permisos')->where('user_id', '=', $request->id)->delete();
                DB::table('comentarios')->where('user_id', '=', $request->id)->delete();
                DB::table('posts')->where('user_id', '=', $request->id)->delete();
                DB::table('users')->where('id', '=', $request->id)->delete();
                if ($eliminado->foto)
                {
                    Log::info($eliminado->foto);
                    Storage::delete('public/'.$eliminado->foto);
                }
                return response()->json(["Se eliminó el usuario:"=>$eliminado]);
            }
            else {
                return response()->json("No se eliminó ningún usuario, verifica que el usuario exista");
            }
        }
        else if ($request->user()->tokenCan('user:delete')) {
            $eliminado = user::where('id', $request->user()->id)->first();
            if ($eliminado) {
                $request->user()->tokens()->delete();
                DB::table('user_permisos')->where('user_id', '=', $request->user()->id)->delete();
                DB::table('comentarios')->where('user_id', '=', $request->user()->id)->delete();
                DB::table('posts')->where('user_id', '=', $request->user()->id)->delete();
                DB::table('users')->where('id', '=', $request->user()->id)->delete();
                if ($eliminado->foto)
                {
                    Storage::delete('public/'.$eliminado->foto);
                }
                return response()->json(["Eliminaste tu usuario:"=>$eliminado]);
            }
            else {
                return response()->json("No se eliminó ningún usuario, verifica que el usuario exista");
            }
        }
    }

    public function logIn(Request $request )
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        $user = User::where('email', $request->email)->first();
        $user_permisos = DB::table('user_permisos')
            ->join('users', 'user_permisos.user_id', '=', 'users.id')
            ->join('permisos', 'user_permisos.permiso_id', '=', 'permisos.id')
            ->select('permisos.tipo')
            ->where('users.id', $user->id)
            ->get()
            ->pluck('tipo')
            ->toArray();
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Usuario o contraseña incorrecta...'],
            ]);
        }
        $token = $user->createToken($request->email, $user_permisos)->plainTextToken;
        return response()->json(["token" => $token], 201);
        
    }

    public function logOut(Request $request )
    {
        return response()->json(["Afectados" => $request->user()->tokens()->delete()], 200);   
    }
    public $data;
    public function registro(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if (User::where('email', '=', $request->email)->first())
        {
            return abort(400, "Este correo ya está siendo ocupado");
        }
        $user              = new User();
        $user->name        = $request->name;
        $user->age         = $request->age;
        $user->email       = $request->email;
        $user->password    = Hash::make($request->password);
        $user->codigo      = Str::random(25);
        if ($request->hasFile('foto')) {
            switch($request->foto->extension()){
                case "jpeg":
                    $path = Storage::disk('public')->putFile('fotos_usuarios', $request->foto);
                break;
                case "jpg":
                    $path = Storage::disk('public')->putFile('fotos_usuarios', $request->foto);
                break;
                case "heic":
                    $path = Storage::disk('public')->putFile('fotos_usuarios', $request->foto);
                break;
                case "png":
                    $path = Storage::disk('public')->putFile('fotos_usuarios', $request->foto);
                break;
                default:
                    return response()->json(["Sólo los archivos .jpeg, .jpg, .png y .heic son compatibles, verifica la extensión."], 400);
                break;
            }
            $user->foto       = $path;
        }
    
        if ($user->save()){
            $data = array (
                'name' => $user->name, 
                'email' => $user->email, 
                'codigo' => $user->codigo
            );
            Mail::send('emails.confirmarcorreo', $data, function ($message) use ($data) {
                $message->from('19170089@uttcampus.edu.mx', 'Ariana Esquivel');
                $message->to($data['email'], $data['name'])->subject('Confirmar cuenta');
            });
            return response()->json(["Se registró el usuario, verifique su correo para confirmar" => $user], 201);
        }
        return abort(400, "Error al registrar usuario");
    }
    public function borrarfoto(Request $request)
    {
        if ($request->user()->tokenCan('admin:delete')) {
            $eliminado = User::select('name', 'foto')->where('id', '=', $request->id)->first();
            if ($eliminado) {
                if ($eliminado->foto) {
                    Storage::delete('public/'.$eliminado->foto);
                    DB::table('users')->where('id', $request->id)
                                ->update(['foto' => null]);
                    return response()->json(["Se eliminó la foto del usuario:"=>$eliminado]);
                }
            }
            return response()->json("No se eliminó ningúna foto, verifica que el usuario exista o tenga foto");
        }
        else if ($request->user()->tokenCan('user:delete')) {
            $eliminado = user::where('id', $request->user()->id)->first();
            $eliminado = User::select('name', 'foto')->where('id', '=', $request->id)->first();
            if ($eliminado) {
                if ($eliminado->foto) {
                    Storage::delete('public/'.$eliminado->foto);
                    DB::table('users')->where('id', $request->id)
                                ->update(['foto' => null]);
                    return response()->json(["Se eliminó la foto del usuario:"=>$eliminado]);
                }
            }
            return response()->json("No se eliminó tu foto, verifica que tengas tengas foto");
        }
    }

    public function cambiarfoto(Request $request)
    {
        if ($request->user()->tokenCan('admin:update')) {
            $antes = user::select('name', 'foto')->where('id', $request->id)->first();
            if (!$antes)
            {
                return abort(400, "Verifica que el id sea existentes");
            }
            if ($request->hasFile('foto')) {
                switch($request->foto->extension()){
                    case "jpeg":
                        $path = Storage::disk('public')->putFile('fotos_usuarios', $request->foto);
                    break;
                    case "jpg":
                        $path = Storage::disk('public')->putFile('fotos_usuarios', $request->foto);
                    break;
                    case "heic":
                        $path = Storage::disk('public')->putFile('fotos_usuarios', $request->foto);
                    break;
                    case "png":
                        $path = Storage::disk('public')->putFile('fotos_usuarios', $request->foto);
                    break;
                    default:
                        return response()->json(["Sólo los archivos .jpeg, .jpg, .png y .heic son compatibles, verifica la extensión."], 400);
                    break;
                }
                if ($path) {
                    Storage::delete('public/'.$antes->foto);
                    DB::table('users') ->where('id', $request->id)->update(['foto' => $path]);
                    $despues = user::select('name', 'foto')->where('id', $request->id)->first();
                }
            }
            if ($despues) {
                return response()->json(["Se editó la foto de:"=>$antes,"a:"=>$despues], 201);
            }
            return abort(400, "Error al editar foto, verifique que los datos sean correctos
            los campos incluyendo el id y que este pertenezca a un usuario");
        }
        else if ($request->user()->tokenCan('user:update')) {
            $antes = user::select('name', 'foto')->where('id', $request->user()->id)->first();
            if ($request->hasFile('foto')) {
                switch($request->foto->extension()){
                    case "jpeg":
                        $path = Storage::disk('public')->putFile('fotos_usuarios', $request->foto);
                    break;
                    case "jpg":
                        $path = Storage::disk('public')->putFile('fotos_usuarios', $request->foto);
                    break;
                    case "heic":
                        $path = Storage::disk('public')->putFile('fotos_usuarios', $request->foto);
                    break;
                    case "png":
                        $path = Storage::disk('public')->putFile('fotos_usuarios', $request->foto);
                    break;
                    default:
                        return response()->json(["Sólo los archivos .jpeg, .jpg, .png y .heic son compatibles, verifica la extensión."], 400);
                    break;
                }
                if ($path) {
                    Storage::delete('public/'.$antes->foto);
                    DB::table('users') ->where('id', $request->user()->id)->update(['foto' => $path]);
                    $despues = user::select('name', 'foto')->where('id', $request->user()->id)->first();
                }
            }
            if ($despues) {
                return response()->json(["Se editó tu foto de:"=>$antes,"a:"=>$despues ], 201);
            }
            return abort(400, "Error al editar tu foto");
        }
        return abort(401, "No tienes autorización para cambiar fotos");
    }

    public function verificarcuenta($codigo)
    {
        $user = User::where('codigo', $codigo)->first();
        if($user)
        {
            $permisos = DB::table('permisos')->select('id')->where('tipo', 'like', 'user'.':%')->get()->pluck('id')
            ->toArray();
            Log::info(["permisos"=>$permisos]);
            for($i = 0; $i < count($permisos); $i++)
            {
                $userpermiso = DB::table('user_permisos')
                ->join('users', 'user_permisos.user_id', '=', 'users.id')
                ->join('permisos', 'user_permisos.permiso_id', '=', 'permisos.id')
                ->select('permisos.id')
                ->where('permisos.id', '=', $permisos[$i])
                ->where('users.id', '=', $user->id)
                ->first();

                if (!$userpermiso)
                {
                    $id_permi = $permisos[$i];
                    $user_permiso                 = new User_Permiso();
                    $user_permiso->user_id        = $user->id;
                    $user_permiso->permiso_id     = $id_permi;
                    $hecho = $user_permiso->save();
                }
            }
            $user->email_verified_at = Carbon::now();
            if ($user->save()){
                return response()->json(["Felicidades, su cuenta ha sido verificada" => $user], 200);
            }
        }
        return response()->json(["Algo ha salido mal, tal vez tu codigo caducó" => $user], 400);
    }

}
