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
    $query_delete = "DELETE FROM drivers WHERE id = $1";
    pg_query_params($conexion, $query_delete, array($id_eliminar));
    header("Location: index.php");
    exit();
}

// Guardar registro (con enlace web de imagen)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['admin'])) {
    $marca = $_POST['marca'] ?? '';
    $modelo = $_POST['modelo'] ?? '';
    $sistema = $_POST['sistema'] ?? '';
    $arquitectura = $_POST['arquitectura'] ?? '';
    $enlace_terabox = $_POST['enlace_terabox'] ?? '';
    $imagen_url = $_POST['imagen_url'] ?? '';

    if (!empty($marca) && !empty($modelo) && !empty($enlace_terabox)) {
        $query = "INSERT INTO drivers (marca, modelo, sistema, arquitectura, enlace_terabox, imagen_url) VALUES ($1, $2, $3, $4, $5, $6)";
        pg_query_params($conexion, $query, array($marca, $modelo, $sistema, $arquitectura, $enlace_terabox, $imagen_url));
        header("Location: index.php");
        exit();
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
    <title>Drivers Hub - Catálogo</title>
    <link rel="icon" type="image/x-icon" href="https://cdn-icons-png.flaticon.com/512/715/715697.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        /* Fondo casi blanco y texto oscuro legible */
        body { background-color: #f8fafc; color: #0f172a; font-family: system-ui, -apple-system, sans-serif; }
        
        .navbar-custom { background-color: #ffffff !important; border-bottom: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        
        .card-driver { 
            border-radius: 16px; 
            border: 1px solid #e2e8f0; 
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            background: #ffffff;
            color: #0f172a;
        }
        .card-driver:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .printer-img-container {
            height: 160px;
            background: #f1f5f9;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 15px 0;
            border: 1px solid #e2e8f0;
        }
        .printer-img-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 8px;
        }
        .form-control, .form-select { 
            border-radius: 10px; 
            padding: 0.65rem 1rem; 
            background-color: #ffffff; 
            border: 1px solid #cbd5e1; 
            color: #0f172a;
        }
        .form-control:focus, .form-select:focus {
            background-color: #ffffff;
            color: #0f172a;
            border-color: #3b82f6;
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
        }
        .badge-custom { background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
    </style>
</head>
<body>

<nav class="navbar navbar-custom py-3 mb-4">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center">
            <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 me-3 d-flex align-items-center justify-content-center border border-primary border-opacity-25" style="width: 48px; height: 48px;">
                <i class="bi bi-printer-fill fs-4"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-bold text-dark">Drivers</h4>
                <small class="text-muted">Gestión centralizada de instaladores</small>
            </div>
        </div>
        <div>
            <?php if (isset($_SESSION['admin'])): ?>
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 me-2 px-3 py-2 rounded-pill fw-semibold">
                    <i class="bi bi-shield-check me-1"></i> Modo Admin
                </span>
                <a href="index.php?logout=true" class="btn btn-outline-danger btn-sm fw-semibold">
                    <i class="bi bi-box-arrow-right me-1"></i> Salir
                </a>
            <?php else: ?>
                <a href="login.php" class="btn btn-dark border btn-sm text-light fw-semibold shadow-sm">
                    <i class="bi bi-lock-fill text-primary me-1"></i> Acceso Admin
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container-fluid px-4">
    <div class="row g-4">
        <?php if (isset($_SESSION['admin'])): ?>
        <div class="col-lg-4">
            <div class="card card-driver p-4 sticky-top" style="top: 20px;">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary text-white rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="bi bi-plus-lg"></i>
                    </div>
                    <h5 class="fw-bold mb-0">Registrar Driver</h5>
                </div>
                
                <form action="index.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Marca de Impresora</label>
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
                        <label class="form-label small fw-bold text-muted">Modelo exacto</label>
                        <input type="text" name="modelo" class="form-control" placeholder="Ej: L3250" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Sistema Operativo</label>
                        <select name="sistema" class="form-select" required>
                            <option value="Windows 11">Windows 11</option>
                            <option value="Windows 10">Windows 10</option>
                            <option value="Windows 8/7">Windows 8/7</option>
                            <option value="Linux">Linux</option>
                            <option value="macOS">macOS</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Arquitectura</label>
                        <select name="arquitectura" class="form-select" required>
                            <option value="64-bits">64-bits</option>
                            <option value="32-bits">32-bits</option>
                            <option value="Universal">Universal</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Enlace de Descarga (Terabox)</label>
                        <input type="url" name="enlace_terabox" class="form-control" placeholder="https://..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Enlace de la Imagen (URL)</label>
                        <input type="url" name="imagen_url" class="form-control" placeholder="https://...">
                        <small class="text-muted" style="font-size: 0.75rem;">Click derecho en Google > "Copiar dirección de imagen"</small>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2 mt-2 shadow-sm rounded-3">
                        <i class="bi bi-save me-2"></i>Guardar Driver
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <div class="<?php echo isset($_SESSION['admin']) ? 'col-lg-8' : 'col-lg-12'; ?>">
            <div class="mb-3">
                <h5 class="fw-bold"><i class="bi bi-folder2-open me-2 text-primary"></i>Drivers Almacenados</h5>
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
                                        <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($row['modelo']); ?></h5>
                                    </div>
                                    <?php if (isset($_SESSION['admin'])): ?>
                                        <div>
                                            <a href="editar.php?id=<?php echo $row['id']; ?>" class="text-primary me-2" title="Editar"><i class="bi bi-pencil-square fs-5"></i></a>
                                            <a href="index.php?eliminar=<?php echo $row['id']; ?>" class="text-danger" title="Eliminar" onclick="return confirm('¿Borrar este driver?');"><i class="bi bi-trash fs-5"></i></a>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex gap-2 mb-2">
                                    <span class="badge badge-custom small"><i class="bi bi-laptop me-1"></i><?php echo htmlspecialchars($row['sistema']); ?></span>
                                    <span class="badge badge-custom small"><i class="bi bi-cpu me-1"></i><?php echo htmlspecialchars($row['arquitectura']); ?></span>
                                </div>

                                <div class="printer-img-container">
                                    <?php if (!empty($row['imagen_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($row['imagen_url']); ?>" alt="Impresora">
                                    <?php else: ?>
                                        <i class="bi bi-printer display-4 text-muted opacity-50"></i>
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
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
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
