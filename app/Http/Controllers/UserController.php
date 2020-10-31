<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;


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
            $eliminado = user::where('id', $request->id)->first();
            DB::table('users')->where('id', '=', $request->id)->delete();
            if ($eliminado) {
                return response()->json(["Se eliminó el usuario:"=>$eliminado]);
            }
            else {
                return response()->json("No se eliminó ningún usuario, verifica que el usuario exista");
            }
        }
        else if ($request->user()->tokenCan('user:delete')) {
            $eliminado = user::where('id', $request->user()->id)->first();
            DB::table('users')->where('id', '=', $request->user()->id)->delete();
            if ($eliminado) {
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

    public function registro(Request $request )
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        $user              = new User();
        $user->name        = $request->name;
        $user->age         = $request->age;
        $user->email       = $request->email;
        $user->password    = Hash::make($request->password);
        if ($user->save())
            return response()->json($user, 201);
        
        return abort(400, "Error al registrar usuario");
        
    }

    public function cuenta()
    {
        return User::all();
    }


}
