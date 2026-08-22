<?php
session_start();
include('conexion.php');

// Cerrar sesión si se solicita
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Eliminar registro (Solo si es admin)
if (isset($_GET['eliminar']) && isset($_SESSION['admin'])) {
    $id_eliminar = $_GET['eliminar'];
    $query_delete = "DELETE FROM drivers WHERE id = $1";
    pg_query_params($conexion, $query_delete, array($id_eliminar));
    header("Location: index.php");
    exit();
}

// Guardar el registro si se envió el formulario (Solo si es admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['admin'])) {
    $marca = $_POST['marca'] ?? '';
    $modelo = $_POST['modelo'] ?? '';
    $sistema = $_POST['sistema'] ?? '';
    $arquitectura = $_POST['arquitectura'] ?? '';
    $enlace_terabox = $_POST['enlace_terabox'] ?? '';

    if (!empty($marca) && !empty($modelo) && !empty($enlace_terabox)) {
        $query = "INSERT INTO drivers (marca, modelo, sistema, arquitectura, enlace_terabox) VALUES ($1, $2, $3, $4, $5)";
        $result = pg_query_params($conexion, $query, array($marca, $modelo, $sistema, $arquitectura, $enlace_terabox));

        if ($result) {
            header("Location: index.php");
            exit();
        }
    }
}

// Obtener la lista de drivers guardados
$sql = "SELECT * FROM drivers ORDER BY id DESC";
$resultado = pg_query($conexion, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drivers Hub - Sistema Centralizado</title>
    <link rel="icon" type="image/x-icon" href="https://cdn-icons-png.flaticon.com/512/715/715697.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        :root {
            --bs-primary: #0d6efd;
            --bg-color: #f8f9fa;
        }
        body { 
            background-color: var(--bg-color); 
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }
        .navbar-custom {
            background: #ffffff;
            border-bottom: 1px solid #eaeaea;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .card { 
            border-radius: 16px; 
            border: none; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            transition: transform 0.2s ease;
        }
        .table-custom th {
            font-weight: 600;
            color: #6c757d;
            background-color: #fcfdfe !important;
            border-bottom: 2px solid #edf2f7;
        }
        .table-custom td {
            vertical-align: middle;
            padding: 1rem 0.75rem;
        }
        .btn-action {
            border-radius: 8px;
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }
        .form-control, .form-select {
            border-radius: 10px;
            padding: 0.65rem 1rem;
            border: 1px solid #dee2e6;
        }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
        }
    </style>
</head>
<body>

<!-- Navbar Superior Estilizada -->
<nav class="navbar navbar-custom py-3 mb-4">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center">
            <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                <i class="bi bi-printer-fill fs-4"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-bold text-dark">Drivers Hub</h4>
                <small class="text-muted">Gestión centralizada de instaladores en Mega</small>
            </div>
        </div>
        <div>
            <?php if (isset($_SESSION['admin'])): ?>
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 me-2 px-3 py-2 rounded-pill fw-semibold">
                    <i class="bi bi-shield-check me-1"></i> Modo Admin
                </span>
                <a href="index.php?logout=true" class="btn btn-outline-danger btn-action fw-semibold">
                    <i class="bi bi-box-arrow-right me-1"></i> Salir
                </a>
            <?php else: ?>
                <a href="login.php" class="btn btn-light border btn-action text-dark fw-semibold shadow-sm">
                    <i class="bi bi-lock-fill text-primary me-1"></i> Acceso Admin
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container-fluid px-4">
    <div class="row g-4">
        <!-- Formulario (SOLO SE MUESTRA SI ES ADMIN) -->
        <?php if (isset($_SESSION['admin'])): ?>
        <div class="col-lg-4">
            <div class="card p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary text-white rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="bi bi-plus-lg"></i>
                    </div>
                    <h5 class="fw-bold mb-0 text-dark">Registrar Driver</h5>
                </div>
                
                <form action="index.php" method="POST">
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
                        <input type="text" name="modelo" class="form-control" placeholder="Ej: LaserJet P1102w" required>
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
                        <label class="form-label small fw-bold text-secondary">Enlace de Descarga (Mega)</label>
                        <input type="url" name="enlace_terabox" class="form-control" placeholder="https://mega.nz/file/..." required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2 mt-2 shadow-sm rounded-3">
                        <i class="bi bi-save me-2"></i>Guardar Driver
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Lista de Drivers -->
        <div class="<?php echo isset($_SESSION['admin']) ? 'col-lg-8' : 'col-lg-12'; ?>">
            <div class="card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-secondary bg-opacity-10 text-secondary rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-folder2-open"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-0">Drivers Almacenados</h5>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Marca / Modelo</th>
                                <th>S.O.</th>
                                <th>Arquitectura</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($resultado && pg_num_rows($resultado) > 0): 
                                while ($row = pg_fetch_assoc($resultado)): 
                            ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['marca']); ?></div>
                                        <div class="small text-muted"><?php echo htmlspecialchars($row['modelo']); ?></div>
                                    </td>
                                    <td>
                                        <span class="text-secondary"><?php echo htmlspecialchars($row['sistema']); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1"><?php echo htmlspecialchars($row['arquitectura']); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?php echo htmlspecialchars($row['enlace_terabox']); ?>" target="_blank" class="btn btn-sm btn-success text-white fw-semibold px-3 me-1 shadow-sm" style="border-radius: 8px;" title="Descargar">
                                            <i class="bi bi-download me-1"></i> Descargar
                                        </a>
                                        <?php if (isset($_SESSION['admin'])): ?>
                                            <a href="editar.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-light border text-primary me-1 shadow-sm" style="border-radius: 8px;" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="index.php?eliminar=<?php echo $row['id']; ?>" class="btn btn-sm btn-light border text-danger shadow-sm" style="border-radius: 8px;" title="Eliminar" onclick="return confirm('¿Estás seguro de borrar este driver?');">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php 
                                endwhile; 
                            else: 
                            ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2 text-black-50"></i>
                                        No hay drivers registrados aún en el repositorio.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
