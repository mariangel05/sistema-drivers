<?php
session_start();

// Contraseña secreta
$password_correcta = "tecnico0506";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password_ingresada = $_POST['password'] ?? '';
    
    if ($password_ingresada === $password_correcta) {
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
    <title>Acceso Admin - Drivers Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8fafc; color: #0f172a; font-family: system-ui, -apple-system, sans-serif; }
        .card-login { border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); background: #ffffff; }
        .form-control { border-radius: 10px; padding: 0.65rem 1rem; background-color: #ffffff; border: 1px solid #cbd5e1; color: #0f172a; }
        .form-control:focus { background-color: #ffffff; color: #0f172a; border-color: #3b82f6; box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25); }
    </style>
</head>
<body>

<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="col-md-5 col-lg-4">
        <div class="card card-login p-4">
            <div class="text-center mb-4">
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle d-inline-flex align-items-center justify-content-center border border-primary border-opacity-25 mb-2" style="width: 64px; height: 64px;">
                    <i class="bi bi-shield-lock-fill fs-3"></i>
                </div>
                <h4 class="fw-bold mb-1">Zona Restringida</h4>
                <p class="text-muted small">Introduce la contraseña de administrador</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 small text-center rounded-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Contraseña" required autofocus>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm rounded-3 mb-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Entrar
                </button>
            </form>

            <div class="text-center">
                <a href="index.php" class="text-decoration-none text-muted small fw-semibold">
                    <i class="bi bi-arrow-left me-1"></i> Volver al catálogo
                </a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
