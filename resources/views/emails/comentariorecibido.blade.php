<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comentario recibido</title>
</head>
<body>
<h2>Comentario recibido</h2>
<p>   
Estimado(a) {{$post_autor}} le informamos su post ha recibido un comentario
</p>
    
<table style='font-size:14px;'>
<tr>
    <td>Título de tu post:</td>
    <td>{{$post_titulo}}</td>
</tr>
<tr>
    <td>Descripción de tu post:</td>
    <td>{{$post_descripcion}}</td>
</tr>
<tr>
    <td>Comentario recibido:</td>
    <td>{{$comentario_comentario}}</td>
</tr>
<tr>
    <td>Autor del comentario:</td>
    <td>{{$comentario_autor}}</td>
</tr>
</table>
</body>
</html>