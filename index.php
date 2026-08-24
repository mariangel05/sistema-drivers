<?php
session_start();
include('conexion.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Drivers Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card p-4 shadow">
            <h1 class="text-primary fw-bold">¡Sistema de Drivers Activo!</h1>
            <p class="text-secondary">La página ya está funcionando correctamente sin bucles.</p>
            <hr>
            <a href="login.php" class="btn btn-dark w-25">Ir al Login de Admin</a>
        </div>
    </div>
</body>
</html>
