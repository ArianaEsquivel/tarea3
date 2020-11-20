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
Estimado(a) <?php echo e($comentario_autor); ?> le informamos su comentario ha sido exitoso
</p>
    
<table style='font-size:14px;'>
<tr>
    <td>Título del post:</td>
    <td><?php echo e($post_titulo); ?></td>
</tr>
<tr>
    <td>Descripción del post:</td>
    <td><?php echo e($post_descripcion); ?></td>
</tr>
<tr>
    <td>Autor del post:</td>
    <td><?php echo e($post_autor); ?></td>
</tr>
<tr>
    <td>Tu comentario:</td>
    <td><?php echo e($comentario_comentario); ?></td>
</tr>
</table>
</body>
</html><?php /**PATH /Users/arianaesquivel/Sites/practica_3/resources/views/emails/comentariocreado.blade.php ENDPATH**/ ?>