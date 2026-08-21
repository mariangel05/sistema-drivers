<?php
include(__DIR__ . "/conexion.php");

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = (int)$_GET['id'];
$res = pg_query($conexion, "SELECT * FROM drivers WHERE id = $id");
$driver = pg_fetch_assoc($res);

if (!$driver) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_driver'])) {
    $marca = pg_escape_string($conexion, $_POST['marca']);
    $modelo = pg_escape_string($conexion, $_POST['modelo']);
    $sistema_operativo = pg_escape_string($conexion, $_POST['sistema_operativo']);
    $arquitectura = pg_escape_string($conexion, $_POST['arquitectura']);
    $tipo_descarga = $_POST['tipo_descarga'];
    $ruta_o_link = $driver['ruta_o_link'];

    if ($tipo_descarga === 'archivo' && isset($_FILES['archivo']) && $_FILES['archivo']['error'] === 0) {
        if ($driver['tipo_descarga'] === 'archivo' && file_exists($driver['ruta_o_link'])) {
            unlink($driver['ruta_o_link']);
        }
        $nombre_archivo = time() . "_" . basename($_FILES['archivo']['name']);
        $destino = "archivos/" . $nombre_archivo;
        if (move_uploaded_file($_FILES['archivo']['tmp_name'], $destino)) {
            $ruta_o_link = $destino;
        }
    } else if ($tipo_descarga === 'enlace' && !empty($_POST['enlace_url'])) {
        $ruta_o_link = pg_escape_string($conexion, $_POST['enlace_url']);
    }

    $query = "UPDATE drivers SET 
              marca='$marca', 
              modelo='$modelo', 
              sistema_operativo='$sistema_operativo', 
              arquitectura='$arquitectura', 
              tipo_descarga='$tipo_descarga', 
              ruta_o_link='$ruta_o_link' 
              WHERE id = $id";
              
    pg_query($conexion, $query);
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Driver</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; padding: 40px; }
        .card { max-width: 450px; margin: 0 auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        input, select, button { width: 100%; padding: 10px; margin-top: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        button { background: #007bff; color: white; border: none; font-weight: bold; cursor: pointer; }
        button:hover { background: #0056b3; }
        .btn-cancel { display: block; text-align: center; margin-top: 10px; color: #666; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>

<div class="card">
    <h2>Editar Driver</h2>
    <form method="POST" enctype="multipart/form-data">
        <select name="marca" required>
            <option value="HP" <?= $driver['marca'] == 'HP' ? 'selected' : '' ?>>HP</option>
            <option value="Epson" <?= $driver['marca'] == 'Epson' ? 'selected' : '' ?>>Epson</option>
            <option value="Canon" <?= $driver['marca'] == 'Canon' ? 'selected' : '' ?>>Canon</option>
            <option value="Kyocera" <?= $driver['marca'] == 'Kyocera' ? 'selected' : '' ?>>Kyocera</option>
            <option value="Brother" <?= $driver['marca'] == 'Brother' ? 'selected' : '' ?>>Brother</option>
            <option value="Otra" <?= $driver['marca'] == 'Otra' ? 'selected' : '' ?>>Otra</option>
        </select>
        
        <input type="text" name="modelo" value="<?= htmlspecialchars($driver['modelo']) ?>" required>
        
        <select name="sistema_operativo" required>
            <option value="Windows 11" <?= $driver['sistema_operativo'] == 'Windows 11' ? 'selected' : '' ?>>Windows 11</option>
            <option value="Windows 10" <?= $driver['sistema_operativo'] == 'Windows 10' ? 'selected' : '' ?>>Windows 10</option>
            <option value="Windows 7 / 8" <?= $driver['sistema_operativo'] == 'Windows 7 / 8' ? 'selected' : '' ?>>Windows 7 / 8</option>
            <option value="Linux / Mac" <?= $driver['sistema_operativo'] == 'Linux / Mac' ? 'selected' : '' ?>>Linux / Mac</option>
        </select>
        
        <select name="arquitectura">
            <option value="64-bits" <?= $driver['arquitectura'] == '64-bits' ? 'selected' : '' ?>>64-bits</option>
            <option value="32-bits" <?= $driver['arquitectura'] == '32-bits' ? 'selected' : '' ?>>32-bits</option>
            <option value="Ambos" <?= $driver['arquitectura'] == 'Ambos' ? 'selected' : '' ?>>Ambos / Universal</option>
        </select>

        <label style="display:block; margin-top:10px; font-size:13px; font-weight:bold;">Origen:</label>
        <select name="tipo_descarga" id="tipo_descarga" onchange="toggleOrigen()">
            <option value="archivo" <?= $driver['tipo_descarga'] == 'archivo' ? 'selected' : '' ?>>Subir nuevo archivo (.exe / .zip)</option>
            <option value="enlace" <?= $driver['tipo_descarga'] == 'enlace' ? 'selected' : '' ?>>Enlace Web Oficial</option>
        </select>

        <div id="campo_archivo" style="margin-top:10px;">
            <input type="file" name="archivo">
            <small style="color:#777;">Deja vacío para mantener el archivo actual.</small>
        </div>

        <div id="campo_enlace" style="display:none; margin-top:10px;">
            <input type="url" name="enlace_url" value="<?= $driver['tipo_descarga'] == 'enlace' ? htmlspecialchars($driver['ruta_o_link']) : '' ?>" placeholder="https://sitio-oficial.com/driver.exe">
        </div>

        <button type="submit" name="actualizar_driver">Guardar Cambios</button>
        <a href="index.php" class="btn-cancel">Cancelar</a>
    </form>
</div>

<script>
function toggleOrigen() {
    const tipo = document.getElementById('tipo_descarga').value;
    document.getElementById('campo_archivo').style.display = (tipo === 'archivo') ? 'block' : 'none';
    document.getElementById('campo_enlace').style.display = (tipo === 'enlace') ? 'block' : 'none';
}
toggleOrigen();
</script>

</body>
</html>