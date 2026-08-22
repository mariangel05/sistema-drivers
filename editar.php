<?php
session_start();
include('conexion.php');

// Verificar si es administrador
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}

// Obtener el ID del driver
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

// Obtener los datos actuales del driver
$query = "SELECT * FROM drivers WHERE id = $1";
$resultado = pg_query_params($conexion, $query, array($id));
$driver = pg_fetch_assoc($resultado);

if (!$driver) {
    header("Location: index.php");
    exit();
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marca = $_POST['marca'] ?? '';
    $modelo = $_POST['modelo'] ?? '';
    $sistema = $_POST['sistema'] ?? '';
    $arquitectura = $_POST['arquitectura'] ?? '';
    $enlace_terabox = $_POST['enlace_terabox'] ?? '';
    $imagen_url = $driver['imagen_url']; // Mantener la imagen actual por defecto

    // Manejar la subida de una nueva imagen si se seleccionó una
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
                // Borrar la imagen anterior si existía para no ocupar espacio basura
                if (!empty($driver['imagen_url']) && file_exists($driver['imagen_url'])) {
                    unlink($driver['imagen_url']);
                }
                $imagen_url = $dest_path;
            }
        }
    }

    if (!empty($marca) && !empty($modelo) && !empty($enlace_terabox)) {
        $update_query = "UPDATE drivers SET marca = $1, modelo = $2, sistema = $3, arquitectura = $4, enlace_terabox = $5, imagen_url = $6 WHERE id = $7";
        $update_result = pg_query_params($conexion, $update_query, array($marca, $modelo, $sistema, $arquitectura, $enlace_terabox, $imagen_url, $id));

        if ($update_result) {
            header("Location: index.php");
            exit();
        }
    }
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
    <style>
        body { background-color: #f8f9fa; font-family: system-ui, -apple-system, sans-serif; }
        .card-driver { border-radius: 16px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.04); background: #ffffff; }
        .form-control, .form-select { border-radius: 10px; padding: 0.65rem 1rem; border: 1px solid #dee2e6; }
        .preview-img { width: 80px; height: 80px; object-fit: contain; background: #f1f3f5; border-radius: 8px; padding: 4px; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card card-driver p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary text-white rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <h5 class="fw-bold mb-0 text-dark">Editar Driver</h5>
                </div>
                
                <!-- IMPORTANTE: enctype="multipart/form-data" para permitir subir archivos -->
                <form action="editar.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Marca de Impresora</label>
                        <select name="marca" class="form-select" required>
                            <?php 
                            $marcas = ["HP", "Epson", "Canon", "Kyocera", "Brother", "Xerox", "Otra"];
                            foreach ($marcas as $m) {
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
                            $sistemas = ["Windows 11", "Windows 10", "Windows 8/7", "Linux", "macOS"];
                            foreach ($sistemas as $s) {
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
                            $arqs = ["64-bits", "32-bits", "Universal"];
                            foreach ($arqs as $a) {
                                $selected = ($driver['arquitectura'] === $a) ? 'selected' : '';
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
                        <label class="form-label small fw-bold text-secondary">Imagen de la Impresora</label>
                        
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <?php if (!empty($driver['imagen_url']) && file_exists($driver['imagen_url'])): ?>
                                <img src="<?php echo htmlspecialchars($driver['imagen_url']); ?>" alt="Actual" class="preview-img border">
                                <small class="text-muted">Imagen actual guardada</small>
                            <?php else: ?>
                                <div class="preview-img d-flex align-items-center justify-content-center text-muted border">
                                    <i class="bi bi-printer fs-3"></i>
                                </div>
                                <small class="text-muted">Sin imagen asignada</small>
                            <?php endif; ?>
                        </div>

                        <input type="file" name="imagen_archivo" class="form-control" accept="image/*">
                        <small class="text-muted" style="font-size: 0.75rem;">Sube una nueva foto si deseas reemplazar la actual.</small>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <a href="index.php" class="btn btn-light border w-50 py-2 fw-semibold">Cancelar</a>
                        <button type="submit" class="btn btn-primary w-50 py-2 fw-semibold shadow-sm">
                            <i class="bi bi-check-lg me-1"></i> Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>
