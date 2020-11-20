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
Estimado(a) <?php echo e($post_autor); ?> le informamos su comentario ha sido exitoso
</p>
    
<table style='font-size:14px;'>
<tr>
    <td>Título de tu post:</td>
    <td><?php echo e($post_titulo); ?></td>
</tr>
<tr>
    <td>Descripción de tu post:</td>
    <td><?php echo e($post_descripcion); ?></td>
</tr>
<tr>
    <td>Comentario recibido:</td>
    <td><?php echo e($comentario_comentario); ?></td>
</tr>
<tr>
    <td>Autor del comentario:</td>
    <td><?php echo e($comentario_autor); ?></td>
</tr>
</table>
</body>
</html><?php /**PATH /Users/arianaesquivel/Sites/practica_3/resources/views/emails/comentariorecibido.blade.php ENDPATH**/ ?>