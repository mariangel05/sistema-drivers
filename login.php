<?php
session_start();
$error = "";

// Contraseña secreta (puedes cambiar 'tu_contraseña_secreta' por la que tú quieras)
$password_correcta = "tecnico0506";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['password'] === $password_correcta) {
        $_SESSION['admin'] = true;
        header("Location: index.php");
        exit();
    } else {
        $error = "Contraseña incorrecta";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Administrador - Drivers </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f6f9; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { border-radius: 12px; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.05); width: 100%; max-width: 400px; }
    </style>
</head>
<body>

<div class="card p-4">
    <div class="text-center mb-4">
        <i class="bi bi-shield-lock-fill text-primary display-4"></i>
        <h4 class="fw-bold mt-2">Zona Restringida</h4>
        <small class="text-muted">Introduce la contraseña para administrar</small>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-2 small text-center"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="mb-3">
            <input type="password" name="password" class="form-control" placeholder="Contraseña de acceso" required autofocus>
        </div>
        <button type="submit" class="btn btn-primary w-100 fw-bold py-2">
            <i class="bi bi-box-arrow-in-right me-2"></i>Entrar
        </button>
    </form>
    
    <div class="text-center mt-3">
        <a href="index.php" class="small text-muted text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Volver al inicio</a>
    </div>
</div>

</body>
</html>
