<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/

/*
Route::get('posts/{id}', 'PostsController@show')-> where("id", "[0-9]+");
Route::get('posts', 'PostsController@index');
Route::post('posts', 'PostsController@store');
Route::delete("posts/{id}", "PostsController@destroy")-> where("id", "[0-9]+");
Route::put("posts/{id}", "PostsController@update")-> where("id", "[0-9]+");

Route::get('comentarios/{id}', 'ComentariosController@show')-> where("id", "[0-9]+");
Route::get('comentarios', 'ComentariosController@index');
Route::post('comentarios', 'ComentariosController@store');
Route::delete("comentarios/{id}", "ComentariosController@destroy")-> where("id", "[0-9]+");
Route::put("comentarios/{id}", "ComentariosController@update")-> where("id", "[0-9]+");

Route::get('users/{id}', 'UserController@show')-> where("id", "[0-9]+");
Route::get('users', 'UserController@index');
Route::post('users', 'UserController@store');
Route::delete("users/{id}", "UserController@destroy")-> where("id", "[0-9]+");
Route::put("users/{id}", "UserController@update")-> where("id", "[0-9]+");
*/

//DESDE AQUÍ COMIENZA LA PRÁCTICA 2//

Route::get('edad', 'UserController@cuenta')-> middleware('validar.edad');

//REGISTRAR Y LOGEAR
Route::post("login", "UserController@logIn");
Route::post("registro", "UserController@registro");

Route::middleware('auth:sanctum')->delete('/logout', 'UserController@logOut');
Route::middleware('auth:sanctum')->get('/cuenta', 'UserController@cuenta');

//PERMISOS ADMIN
// user:update user:index user:delete user:create admin:update admin:index admin:delete admin:create admin:asignar
Route::middleware('auth:sanctum')->get('permisos', 'PermisosController@index');
Route::middleware('auth:sanctum')->post('permisos', 'PermisosController@store');
Route::middleware('auth:sanctum')->put('permisos', 'PermisosController@update');
Route::middleware('auth:sanctum')->delete('permisos', 'PermisosController@destroy');

//USER_PERMISO
Route::middleware('auth:sanctum')->get('asignar', 'UserPermisoController@index');
Route::middleware('auth:sanctum')->post('asignar', 'UserPermisoController@store');
Route::middleware('auth:sanctum')->delete('asignar', 'UserPermisoController@destroy');

//ASIGNAR admin O user
Route::middleware('auth:sanctum')->post('tipospermisos', 'UserPermisoController@tipospermisos');

//USERS
Route::middleware('auth:sanctum')->get('users', 'UserController@index');
Route::middleware('auth:sanctum')->post('users', 'UserController@store');
Route::middleware('auth:sanctum')->put('users', 'UserController@update');
Route::middleware('auth:sanctum')->delete('users', 'UserController@destroy');

//POSTS
Route::middleware('auth:sanctum')->get('posts', 'PostsController@index');
Route::middleware('auth:sanctum')->post('posts', 'PostsController@store');
Route::middleware('auth:sanctum')->put('posts', 'PostsController@update');
Route::middleware('auth:sanctum')->delete('posts', 'PostsController@destroy');

//COMENTARIOS
Route::middleware('auth:sanctum')->get('comentarios', 'ComentariosController@index');
Route::middleware('auth:sanctum')->post('comentarios', 'ComentariosController@store');
Route::middleware('auth:sanctum')->put('comentarios', 'ComentariosController@update');
Route::middleware('auth:sanctum')->delete('comentarios', 'ComentariosController@destroy');

//DESDE AQUÍ COMIENZA LA PRÁCTICA 3//

//FOTOS
Route::middleware('auth:sanctum')->delete('borrarfoto', 'UserController@borrarfoto');
Route::middleware('auth:sanctum')->post('cambiarfoto', 'UserController@cambiarfoto');

//IMAGENES
Route::middleware('auth:sanctum')->delete('borrarimagen', 'PostsController@borrarimagen');
Route::middleware('auth:sanctum')->post('cambiarimagen', 'PostsController@cambiarimagen');

//
Route::get("verificarcuenta/{codigo}", "UserController@verificarcuenta");

//RUTAS DE API NASA
Route::get("epic", "NasaController@EPIC");
Route::get("eart", "NasaController@EART");
Route::get("insight", "NasaController@InSight");

