<?php
session_start();
include('conexion.php');

// Verificar si es administrador
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php");
    exit();
}

// Obtener datos actuales del driver
$query = "SELECT * FROM drivers WHERE id = $1";
$resultado = pg_query_params($conexion, $query, array($id));
$driver = pg_fetch_assoc($resultado);

if (!$driver) {
    header("Location: index.php");
    exit();
}

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marca = $_POST['marca'] ?? '';
    $modelo = $_POST['modelo'] ?? '';
    $sistema = $_POST['sistema'] ?? '';
    $arquitectura = $_POST['arquitectura'] ?? '';
    $enlace_terabox = $_POST['enlace_terabox'] ?? '';
    $imagen_url = $_POST['imagen_url'] ?? '';

    if (!empty($marca) && !empty($modelo) && !empty($enlace_terabox)) {
        $update_query = "UPDATE drivers SET marca = $1, modelo = $2, sistema = $3, arquitectura = $4, enlace_terabox = $5, imagen_url = $6 WHERE id = $7";
        pg_query_params($conexion, $update_query, array($marca, $modelo, $sistema, $arquitectura, $enlace_terabox, $imagen_url, $id));
        header("Location: index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Driver - Drivers Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #0f172a; color: #f8fafc; font-family: system-ui, -apple-system, sans-serif; }
        .card-custom { border-radius: 16px; border: 1px solid #334155; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3); background: #1e293b; color: #f8fafc; }
        .form-control, .form-select { border-radius: 10px; padding: 0.65rem 1rem; background-color: #0f172a; border: 1px solid #334155; color: #f8fafc; }
        .form-control:focus, .form-select:focus { background-color: #0f172a; color: #f8fafc; border-color: #3b82f6; box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25); }
    </style>
</head>
<body>

<div class="container d-flex align-items-center justify-content-center min-vh-100 py-4">
    <div class="col-md-6 col-lg-5">
        <div class="card card-custom p-4">
            <div class="d-flex align-items-center mb-4">
                <div class="bg-primary bg-opacity-25 text-primary p-2 rounded-circle me-3 d-flex align-items-center justify-content-center border border-primary border-opacity-25" style="width: 48px; height: 48px;">
                    <i class="bi bi-pencil-square fs-4"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0 text-white">Editar Driver</h4>
                    <small class="text-secondary">Modifica la información del equipo</small>
                </div>
            </div>

            <form action="editar.php?id=<?php echo $id; ?>" method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Marca de Impresora</label>
                    <select name="marca" class="form-select" required>
                        <?php 
                        $marcas = ['HP', 'Epson', 'Canon', 'Kyocera', 'Brother', 'Xerox', 'Otra'];
                        foreach($marcas as $m) {
                            $selected = ($driver['marca'] === $m) ? 'selected' : '';
                            echo "<option value=\"$m\" $selected>$m</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Modelo exacto</label>
                    <input type="text" name="modelo" class="form-control" value="<?php echo htmlspecialchars($driver['modelo']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Sistema Operativo</label>
                    <select name="sistema" class="form-select" required>
                        <?php 
                        $sistemas = ['Windows 11', 'Windows 10', 'Windows 8/7', 'Linux', 'macOS'];
                        foreach($sistemas as $s) {
                            $selected = ($driver['sistema'] === $s) ? 'selected' : '';
                            echo "<option value=\"$s\" $selected>$s</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Arquitectura</label>
                    <select name="arquitectura" class="form-select" required>
                        <?php 
                        $arqs = ['64-bits', '32-bits', 'Universal'];
                        foreach($arqs as $a) {
                            $selected = ($driver['arquitectura'] === $a) ? 'selected' : '';
                            echo "<option value=\"$a\" $selected>$a</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Enlace de Descarga (Terabox)</label>
                    <input type="url" name="enlace_terabox" class="form-control" value="<?php echo htmlspecialchars($driver['enlace_terabox']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Enlace de la Imagen (URL)</label>
                    <input type="url" name="imagen_url" class="form-control" value="<?php echo htmlspecialchars($driver['imagen_url'] ?? ''); ?>" placeholder="https://...">
                    <small class="text-muted" style="font-size: 0.75rem;">Click derecho en Google > "Copiar dirección de imagen"</small>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm rounded-3">
                        <i class="bi bi-check-lg me-2"></i>Guardar Cambios
                    </button>
                    <a href="index.php" class="btn btn-outline-secondary w-100 fw-bold py-2 rounded-3 text-light">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
