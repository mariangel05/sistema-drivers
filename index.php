<?php
include('conexion.php');
// Eliminar registro si se hizo clic en el botón de borrar
if (isset($_GET['eliminar'])) {
    $id_eliminar = $_GET['eliminar'];
    $query_delete = "DELETE FROM drivers WHERE id = $1";
    pg_query_params($conexion, $query_delete, array($id_eliminar));
    header("Location: index.php");
    exit();
}
$mensaje = "";

// Guardar el registro si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marca = $_POST['marca'] ?? '';
    $modelo = $_POST['modelo'] ?? '';
    $sistema = $_POST['sistema'] ?? '';
    $arquitectura = $_POST['arquitectura'] ?? '';
    $enlace_terabox = $_POST['enlace_terabox'] ?? '';

    if (!empty($marca) && !empty($modelo) && !empty($enlace_terabox)) {
        $query = "INSERT INTO drivers (marca, modelo, sistema, arquitectura, enlace_terabox) VALUES ($1, $2, $3, $4, $5)";
        $result = pg_query_params($conexion, $query, array($marca, $modelo, $sistema, $arquitectura, $enlace_terabox));

        if ($result) {
            $mensaje = "Driver registrado con éxito.";
            header("Location: index.php");
            exit();
        } else {
            $mensaje = "Error al guardar el driver.";
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
    <title>Repositorio de Drivers</title>
    <link rel="icon" type="image/x-icon" href="https://cdn-icons-png.flaticon.com/512/715/715697.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f6f9; }
        .card { border-radius: 12px; border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    </style>
</head>
<body class="p-4">

<div class="container-fluid max-width-1200">
    <!-- Encabezado -->
    <div class="d-flex align-items-center mb-4">
        <i class="bi bi-printer-fill text-primary display-5 me-3"></i>
        <div>
            <h2 class="mb-0 fw-bold">Repositorio de Drivers</h2>
            <small class="text-muted">Gestión centralizada de instaladores alojados en Mega</small>
        </div>
    </div>

    <div class="row g-4">
        <!-- Formulario -->
        <div class="col-md-4">
            <div class="card p-4">
                <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-plus-circle me-2"></i>Registrar Driver</h5>
                
                <form action="index.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Marca de Impresora</label>
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
                        <label class="form-label small fw-semibold">Modelo exacto</label>
                        <input type="text" name="modelo" class="form-control" placeholder="Ej: LaserJet P1102w" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Sistema Operativo</label>
                        <select name="sistema" class="form-select" required>
                            <option value="Windows 11">Windows 11</option>
                            <option value="Windows 10">Windows 10</option>
                            <option value="Windows 8/7">Windows 8/7</option>
                            <option value="Linux">Linux</option>
                            <option value="macOS">macOS</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Arquitectura</label>
                        <select name="arquitectura" class="form-select" required>
                            <option value="64-bits">64-bits</option>
                            <option value="32-bits">32-bits</option>
                            <option value="Universal">Universal</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Enlace de Descarga (Mega)</label>
                        <input type="url" name="enlace_terabox" class="form-control" placeholder="https://mega.nz/file/..." required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2 mt-2">
                        <i class="bi bi-save me-2"></i>Guardar Driver
                    </button>
                </form>
            </div>
        </div>

        <!-- Lista de Drivers -->
        <div class="col-md-8">
            <div class="card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-secondary mb-0"><i class="bi bi-folder2-open me-2"></i>Drivers Almacenados</h5>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Marca / Modelo</th>
                                <th>S.O.</th>
                                <th>Arquitectura</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($resultado && pg_num_rows($resultado) > 0): 
                                while ($row = pg_fetch_assoc($resultado)): 
                            ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['marca']); ?></strong>
                                        <div class="small text-muted"><?php echo htmlspecialchars($row['modelo']); ?></div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['sistema']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['arquitectura']); ?></span></td>
                                    <td>
                                        <a href="<?php echo htmlspecialchars($row['enlace_terabox']); ?>" target="_blank" class="btn btn-sm btn-outline-success me-1" title="Descargar">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <a href="editar.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary me-1" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="index.php?eliminar=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de borrar este driver?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php 
                                endwhile; 
                            else: 
                            ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
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
