<?php
session_start();
include('conexion.php');

// Verificar si es admin
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

// Obtener el ID del driver a editar
$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php");
    exit();
}

// Si se envió el formulario de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marca = $_POST['marca'] ?? '';
    $modelo = $_POST['modelo'] ?? '';
    $sistema = $_POST['sistema'] ?? '';
    $arquitectura = $_POST['arquitectura'] ?? '';
    $enlace_terabox = $_POST['enlace_terabox'] ?? '';

    // Verificar si se subió una nueva imagen
    if (isset($_FILES['imagen_archivo']) && $_FILES['imagen_archivo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['imagen_archivo']['tmp_name'];
        $fileName = $_FILES['imagen_archivo']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowedExtensions = array('jpg', 'jpeg', 'png', 'webp');
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = 'uploads/';
            
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            
            $dest_path = $uploadFileDir . $newFileName;
            
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                // Borrar la imagen anterior si existía
                $res_old = pg_query_params($conexion, "SELECT imagen_url FROM drivers WHERE id = $1", array($id));
                if ($row_old = pg_fetch_assoc($res_old)) {
                    if (!empty($row_old['imagen_url']) && file_exists($row_old['imagen_url'])) {
                        unlink($row_old['imagen_url']);
                    }
                }

                // Actualizar incluyendo la nueva imagen
                $query = "UPDATE drivers SET marca = $1, modelo = $2, sistema = $3, arquitectura = $4, enlace_terabox = $5, imagen_url = $6 WHERE id = $7";
                pg_query_params($conexion, $query, array($marca, $modelo, $sistema, $arquitectura, $enlace_terabox, $dest_path, $id));
            }
        }
    } else {
        // Actualizar sin tocar la imagen si no se seleccionó una nueva
        $query = "UPDATE drivers SET marca = $1, modelo = $2, sistema = $3, arquitectura = $4, enlace_terabox = $5 WHERE id = $6";
        pg_query_params($conexion, $query, array($marca, $modelo, $sistema, $arquitectura, $enlace_terabox, $id));
    }

    header("Location: index.php");
    exit();
}

// Obtener los datos actuales del driver de la base de datos
$resultado = pg_query_params($conexion, "SELECT * FROM drivers WHERE id = $1", array($id));
$driver = pg_fetch_assoc($resultado);

if (!$driver) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Driver - Drivers Hub</title>
    <link rel="icon" type="image/x-icon" href="https://cdn-icons-png.flaticon.com/512/715/715697.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>body { background-color: #0f172a; color: #f8fafc; font-family: system-ui, -apple-system, sans-serif; }
        .navbar-custom { background-color: #1e293b !important; border-bottom: 1px solid #334155; }
        .card-driver { 
            border-radius: 16px; 
            border: 1px solid #334155; 
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
            background: #1e293b;
        }
        .form-control, .form-select { 
            border-radius: 10px; 
            padding: 0.65rem 1rem; 
            background-color: #0f172a; 
            border: 1px solid #334155; 
            color: #f8fafc;
        }
        .form-control:focus, .form-select:focus {
            background-color: #0f172a;
            color: #f8fafc;
            border-color: #3b82f6;
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
        }
        .form-control::placeholder { color: #64748b; }
    </style>
</head>
<body>

<nav class="navbar navbar-custom py-3 mb-4">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center">
            <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 me-3 d-flex align-items-center justify-content-center border border-primary border-opacity-25" style="width: 48px; height: 48px;">
                <i class="bi bi-pencil-square fs-4"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-bold text-white">Editar Driver</h4>
                <small class="text-secondary">Modificar información del equipo</small>
            </div>
        </div>
        <div>
            <a href="index.php" class="btn btn-outline-light btn-sm fw-semibold">
                <i class="bi bi-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>
</nav>

<div class="container py-3" style="max-width: 600px;">
    <div class="card card-driver p-4">
        <form action="editar.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
            
            <div class="mb-3">
                <label class="form-label small fw-bold text-secondary">Marca de Impresora</label>
                <select name="marca" class="form-select" required>
                    <option value="">Seleccionar Marca</option>
                    <?php 
                    $marcas = ['HP', 'Epson', 'Canon', 'Kyocera', 'Brother', 'Xerox', 'Otra'];
                    foreach ($marcas as $m) {
                        $selected = ($driver['marca'] == $m) ? 'selected' : '';
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
                    foreach ($sistemas as $s) {
                        $selected = ($driver['sistema'] == $s) ? 'selected' : '';
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
                    foreach ($arqs as $a) {
                        $selected = ($driver['arquitectura'] == $a) ? 'selected' : '';
                        echo "<option value=\"$a\" $selected>$a</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-secondary">Enlace de Descarga</label>
                <input type="url" name="enlace_terabox" class="form-control" value="<?php echo htmlspecialchars($driver['enlace_terabox']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-secondary">Imagen actual de la Impresora</label>
                <div class="mb-2">
                    <?php if (!empty($driver['imagen_url']) && file_exists($driver['imagen_url'])): ?>
                        <img src="<?php echo htmlspecialchars($driver['imagen_url']); ?>" alt="Actual" style="height: 80px; object-fit: contain; background: #0f172a; padding: 5px; border-radius: 8px; border: 1px solid #334155;">
                    <?php else: ?>
                        <span class="text-secondary small">Sin imagen registrada</span>
                    <?php endif; ?>
                </div>
                <label class="form-label small fw-bold text-secondary">Cambiar imagen (opcional)</label>
                <input type="file" name="imagen_archivo" class="form-control" accept="image/*">
                <small class="text-secondary" style="font-size: 0.75rem;">Si dejas este campo vacío, se conservará la imagen que ya tiene.</small>
            </div>

            <button type="submit" class="btn btn-primary w-100 fw-bold py-2 mt-2 shadow-sm rounded-3">
                <i class="bi bi-save me-2"></i>Actualizar Driver
            </button>
        </form>
    </div>
</div>

</body>
</html>
