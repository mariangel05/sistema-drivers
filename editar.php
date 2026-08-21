<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('conexion.php');
// ... el resto de tu código de editar.php que ya tenías ...

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

    $query_update = "UPDATE drivers SET marca = $1, modelo = $2, sistema = $3, arquitectura = $4, enlace_terabox = $5 WHERE id = $6";
    $result = pg_query_params($conexion, $query_update, array($marca, $modelo, $sistema, $arquitectura, $enlace_terabox, $id));

    if ($result) {
        header("Location: index.php");
        exit();
    }
}

// Obtener los datos actuales del driver
$query_select = "SELECT * FROM drivers WHERE id = $1";
$resultado = pg_query_params($conexion, $query_select, array($id));
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
    <title>Editar Driver</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>body { background-color: #f4f6f9; }</style>
</head>
<body class="p-5">
<div class="container" style="max-width: 500px;">
    <div class="card p-4 shadow-sm border-0 rounded-3">
        <h4 class="fw-bold text-primary mb-3"><i class="bi bi-pencil-square me-2"></i>Editar Driver</h4>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label small fw-semibold">Marca</label>
                <select name="marca" class="form-select" required>
                    <option value="HP" <?php if($driver['marca']=='HP') echo 'selected'; ?>>HP</option>
                    <option value="Epson" <?php if($driver['marca']=='Epson') echo 'selected'; ?>>Epson</option>
                    <option value="Canon" <?php if($driver['marca']=='Canon') echo 'selected'; ?>>Canon</option>
                    <option value="Kyocera" <?php if($driver['marca']=='Kyocera') echo 'selected'; ?>>Kyocera</option>
                    <option value="Brother" <?php if($driver['marca']=='Brother') echo 'selected'; ?>>Brother</option>
                    <option value="Xerox" <?php if($driver['marca']=='Xerox') echo 'selected'; ?>>Xerox</option>
                    <option value="Otra" <?php if($driver['marca']=='Otra') echo 'selected'; ?>>Otra</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Modelo</label>
                <input type="text" name="modelo" class="form-control" value="<?php echo htmlspecialchars($driver['modelo']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Sistema Operativo</label>
                <select name="sistema" class="form-select" required>
                    <option value="Windows 11" <?php if($driver['sistema']=='Windows 11') echo 'selected'; ?>>Windows 11</option>
                    <option value="Windows 10" <?php if($driver['sistema']=='Windows 10') echo 'selected'; ?>>Windows 10</option>
                    <option value="Windows 8/7" <?php if($driver['sistema']=='Windows 8/7') echo 'selected'; ?>>Windows 8/7</option>
                    <option value="Linux" <?php if($driver['sistema']=='Linux') echo 'selected'; ?>>Linux</option>
                    <option value="macOS" <?php if($driver['sistema']=='macOS') echo 'selected'; ?>>macOS</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Arquitectura</label>
                <select name="arquitectura" class="form-select" required>
                    <option value="64-bits" <?php if($driver['arquitectura']=='64-bits') echo 'selected'; ?>>64-bits</option>
                    <option value="32-bits" <?php if($driver['arquitectura']=='32-bits') echo 'selected'; ?>>32-bits</option>
                    <option value="Universal" <?php if($driver['arquitectura']=='Universal') echo 'selected'; ?>>Universal</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Enlace de Descarga</label>
                <input type="url" name="enlace_terabox" class="form-control" value="<?php echo htmlspecialchars($driver['enlace_terabox']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-bold py-2"><i class="bi bi-save me-2"></i>Actualizar Driver</button>
            <a href="index.php" class="btn btn-secondary w-100 mt-2 py-2">Cancelar</a>
        </form>
    </div>
</div>
</body>
</html>
