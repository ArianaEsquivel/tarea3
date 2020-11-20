<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comentario hecho</title>
</head>
<body>
<h2>Comentario exitoso</h2>
<p>   
Estimado(a) {{$comentario_autor}} le informamos su comentario ha sido enviado de manera satisfactoria
</p>
    
<table style='font-size:14px;'>
<tr>
    <td>Título del post:</td>
    <td>{{$post_titulo}}</td>
</tr>
<tr>
    <td>Descripción del post:</td>
    <td>{{$post_descripcion}}</td>
</tr>
<tr>
    <td>Autor del post:</td>
    <td>{{$post_autor}}</td>
</tr>
<tr>
    <td>Tu comentario:</td>
    <td>{{$comentario_comentario}}</td>
</tr>
</table>
</body>
</html>