<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alguien no tiene permiso</title>
</head>
<body>
<h2>Aviso</h2>
<p>   
Estimado administrador le informamos el correo <?php echo e($email); ?> perteneciente a <?php echo e($name); ?> 
está intentando <?php echo e($razón); ?> sin tener el permiso <?php echo e($permiso); ?>.
</p>
</body>
</html><?php /**PATH /Users/arianaesquivel/Sites/practica_3/resources/views/emails/sinpermiso.blade.php ENDPATH**/ ?>