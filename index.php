<?php
session_start();
include('conexion.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password_ingresada = $_POST['password'] ?? '';

    // Consulta segura para obtener la contraseña de la base de datos
    $query = "SELECT valor FROM configuracion WHERE clave = 'admin_password'";
    $resultado = pg_query($conexion, $query);

    if ($resultado && $row = pg_fetch_assoc($resultado)) {
        $password_db = $row['valor'];

        // Verificamos si la contraseña está encriptada (hash) o es texto plano
        $es_valida = false;
        if (password_get_info($password_db)['algo']) {
            $es_valida = password_verify($password_ingresada, $password_db);
        } else {
            // Compatibilidad por si la contraseña se guardó inicialmente en texto plano
            $es_valida = ($password_ingresada === $password_db);
        }

        if ($es_valida) {
            $_SESSION['admin'] = true;
            header("Location: index.php");
            exit();
        } else {
            $error = "Contraseña incorrecta. Inténtalo de nuevo.";
        }
    } else {
        $error = "Error de configuración del sistema.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Admin - Drivers Hub</title>
    <link rel="icon" type="image/x-icon" href="https://cdn-icons-png.flaticon.com/512/715/715697.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        /* Fondo con imagen tecnológica de alta calidad y capa oscura translúcida */
        body { 
            background: linear-gradient(rgba(15, 23, 42, 0.85), rgba(15, 23, 42, 0.90)), 
                        url('https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?q=80&w=1920&auto=format&fit=crop') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, sans-serif;
            color: #f8fafc;
        }

        /* Tarjeta de login elegante y moderna */
        .login-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 420px;
            padding: 2.5rem;
            color: #1e293b;
        }

        .form-control {
            border-radius: 12px;
            padding: 0.75rem 1rem;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
        }
        
        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 0.25rem rgba(37, 99, 235, 0.15);
            background-color: #ffffff;
        }

        .btn-primary {
            border-radius: 12px;
            padding: 0.75rem;
            font-weight: 600;
            background: linear-gradient(135deg, #1e40af, #2563eb);
            border: none;
            transition: opacity 0.2s ease;
        }
        
        .btn-primary:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center">
    <div class="login-card text-center">
        <!-- Icono superior -->
        <div class="mb-3">
            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 70px; height: 70px;">
                <i class="bi bi-shield-lock-fill fs-3"></i>
            </div>
        </div>

        <h3 class="fw-bold text-dark mb-1">Zona Restringida</h3>
        <p class="text-muted small mb-4">Introduce la contraseña para administrar</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small rounded-3 mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="mb-3 text-start">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0" style="border-radius: 12px 0 0 12px; border-color: #cbd5e1;">
                        <i class="bi bi-key text-secondary"></i>
                    </span>
                    <input type="password" name="password" class="form-control border-start-0" placeholder="Contraseña de administrador" style="border-radius: 0 12px 12px 0;" required autofocus>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 shadow-sm mb-3">
                <i class="bi bi-box-arrow-in-right me-2"></i> Entrar
            </button>
        </form>

        <a href="index.php" class="text-decoration-none text-muted small fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Volver al inicio
        </a>
    </div>
</div>

</body>
</html>
