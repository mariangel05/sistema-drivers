<?php
session_start();
include('conexion.php');

// Cerrar sesión
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Eliminar registro (Solo Admin)
if (isset($_GET['eliminar']) && isset($_SESSION['admin'])) {
    $id_eliminar = $_GET['eliminar'];
    
    $res_img = pg_query_params($conexion, "SELECT imagen_url FROM drivers WHERE id = $1", array($id_eliminar));
    if ($row_img = pg_fetch_assoc($res_img)) {
        if (!empty($row_img['imagen_url']) && file_exists($row_img['imagen_url'])) {
            unlink($row_img['imagen_url']);
        }
    }

    $query_delete = "DELETE FROM drivers WHERE id = $1";
    pg_query_params($conexion, $query_delete, array($id_eliminar));
    header("Location: index.php");
    exit();
}

// Guardar registro (Solo Admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['admin'])) {
    $marca = $_POST['marca'] ?? '';
    $modelo = $_POST['modelo'] ?? '';
    $sistema = $_POST['sistema'] ?? '';
    $arquitectura = $_POST['arquitectura'] ?? '';
    $enlace_terabox = $_POST['enlace_terabox'] ?? '';
    $imagen_url = '';

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
                $imagen_url = $dest_path;
            }
        }
    }

    if (!empty($marca) && !empty($modelo) && !empty($enlace_terabox)) {
        $query = "INSERT INTO drivers (marca, modelo, sistema, arquitectura, enlace_terabox, imagen_url) VALUES ($1, $2, $3, $4, $5, $6)";
        $result = pg_query_params($conexion, $query, array($marca, $modelo, $sistema, $arquitectura, $enlace_terabox, $imagen_url));

        if ($result) {
            header("Location: index.php");
            exit();
        }
    }
}

@pg_query($conexion, "ALTER TABLE drivers ADD COLUMN IF NOT EXISTS imagen_url TEXT;");
$sql = "SELECT * FROM drivers ORDER BY id DESC";
$resultado = pg_query($conexion, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drivers - Catálogo</title>
    <link rel="icon" type="image/x-icon" href="https://cdn-icons-png.flaticon.com/512/715/715697.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #e2e8f0; color: #1e293b; font-family: system-ui, -apple-system, sans-serif; }
        .navbar-custom { background: linear-gradient(135deg, #1e40af, #2563eb); box-shadow: 0 4px 12px rgba(30, 64, 175, 0.25); }
        .printer-header-icon {
            background-color: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .card-driver { 
            border-radius: 16px; 
            border: 1px solid #cbd5e1; 
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            background: #ffffff;
        }
        .card-driver:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .printer-img-container {
            height: 160px;
            background: #f8fafc;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 15px 0;
            border: 1px solid #e2e8f0;
        }
        .printer-img-container img { width: 100%; height: 100%; object-fit: contain; padding: 8px; }
        .form-control, .form-select { border-radius: 10px; padding: 0.65rem 1rem; border: 1px solid #cbd5e1; background-color: #ffffff; color: #1e293b; }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-custom py-3 mb-4">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center">
            <div class="printer-header-icon me-3">
                <i class="bi bi-printer-fill fs-4 text-white"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-bold text-white">Drivers</h4>
                <small style="color: #bfdbfe;">Gestión centralizada de instaladores</small>
            </div>
        </div>
        <div>
            <?php if (isset($_SESSION['admin'])): ?>
                <span class="badge bg-white text-primary me-2 px-3 py-2 rounded-pill fw-semibold shadow-sm">
                    <i class="bi bi-shield-check me-1"></i> Modo Admin
                </span>
                <a href="index.php?logout=true" class="btn btn-outline-light btn-sm fw-semibold">
                    <i class="bi bi-box-arrow-right me-1"></i> Salir
                </a>
            <?php else: ?>
                <a href="login.php" class="btn btn-light btn-sm text-primary fw-semibold shadow-sm">
                    <i class="bi bi-lock-fill me-1"></i> Acceso Admin
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container-fluid px-4">
    <div class="row g-4">
        <!-- Formulario (SOLO SE MUESTRA SI ESTÁS LOGUEADO COMO ADMIN) -->
        <?php if (isset($_SESSION['admin'])): ?>
        <div class="col-lg-4">
            <div class="card card-driver p-4 sticky-top" style="top: 20px;">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary text-white rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="bi bi-plus-lg"></i>
                    </div>
                    <h5 class="fw-bold mb-0 text-dark">Registrar Driver</h5>
                </div>
                
                <form action="index.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Marca de Impresora</label>
                        <select name="marca" class="form-select" required>
                            <option value="">Seleccionar Marca</option>
                            <option value="HP">HP</option>
                            <option value="Epson">Epson</option>
                            <option value="Canon">Canon</option>
                            <option value="Kyocera">Kyocera</option>
                            <option value="Brother">Brother</option>
                            <option value="Xerox">Xerox</option>
                            <option value="Otra">Otra</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Modelo exacto</label>
                        <input type="text" name="modelo" class="form-control" placeholder="Ej: L3250" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Sistema Operativo</label>
                        <select name="sistema" class="form-select" required>
                            <option value="Windows 11">Windows 11</option>
                            <option value="Windows 10">Windows 10</option>
                            <option value="Windows 8/7">Windows 8/7</option>
                            <option value="Linux">Linux</option>
                            <option value="macOS">macOS</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Arquitectura</label>
                        <select name="arquitectura" class="form-select" required>
                            <option value="64-bits">64-bits</option>
                            <option value="32-bits">32-bits</option>
                            <option value="Universal">Universal</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Enlace de Descarga</label>
                        <input type="url" name="enlace_terabox" class="form-control" placeholder="https://..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Imagen de la Impresora</label>
                        <input type="file" name="imagen_archivo" class="form-control" accept="image/*">
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2 mt-2 shadow-sm rounded-3">
                        <i class="bi bi-save me-2"></i>Guardar Driver
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tarjetas del Catálogo (Ocupa todo el ancho si no hay sesión, o 8 columnas si eres admin) -->
        <div class="<?php echo isset($_SESSION['admin']) ? 'col-lg-8' : 'col-lg-12'; ?>">
            <div class="mb-3">
                <h5 class="fw-bold text-secondary"><i class="bi bi-folder2-open me-2 text-primary"></i>Drivers Almacenados</h5>
            </div>

            <div class="row g-4">
                <?php 
                if ($resultado && pg_num_rows($resultado) > 0): 
                    while ($row = pg_fetch_assoc($resultado)): 
                ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="card card-driver p-4 h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="badge bg-primary bg-opacity-10 text-primary mb-1 fw-bold"><?php echo htmlspecialchars($row['marca']); ?></span>
                                        <h5 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($row['modelo']); ?></h5>
                                    </div>
                                    <?php if (isset($_SESSION['admin'])): ?>
                                        <div>
                                            <a href="editar.php?id=<?php echo $row['id']; ?>" class="text-primary me-2" title="Editar"><i class="bi bi-pencil-square fs-5"></i></a>
                                            <a href="index.php?eliminar=<?php echo $row['id']; ?>" class="text-danger" title="Eliminar" onclick="return confirm('¿Borrar este driver?');"><i class="bi bi-trash fs-5"></i></a>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex gap-2 mb-2">
                                    <span class="badge bg-light text-secondary border small"><i class="bi bi-laptop me-1"></i><?php echo htmlspecialchars($row['sistema']); ?></span>
                                    <span class="badge bg-light text-secondary border small"><i class="bi bi-cpu me-1"></i><?php echo htmlspecialchars($row['arquitectura']); ?></span>
                                </div>

                                <div class="printer-img-container">
                                    <?php if (!empty($row['imagen_url']) && file_exists($row['imagen_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($row['imagen_url']); ?>" alt="Impresora">
                                    <?php else: ?>
                                        <i class="bi bi-printer display-4 text-secondary opacity-50"></i>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div>
                                <a href="<?php echo htmlspecialchars($row['enlace_terabox']); ?>" target="_blank" class="btn btn-success w-100 fw-semibold text-white shadow-sm py-2" style="border-radius: 10px;">
                                    <i class="bi bi-download me-2"></i> Descargar
                                </a>
                            </div>
                        </div>
                    </div>
                <?php 
                    endwhile; 
                else: 
                ?>
                    <div class="col-12">
                        <div class="card card-driver text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-black-50"></i>
                            No hay drivers registrados aún en el sistema.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
