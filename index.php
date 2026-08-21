<?php
include(__DIR__ . "/conexion.php");

// Lógica para eliminar
if (isset($_GET['eliminar'])) {
    $id_eliminar = (int)$_GET['eliminar'];
    $res = pg_query($conexion, "SELECT tipo_descarga, ruta_o_link FROM drivers WHERE id = $id_eliminar");
    if ($row_del = pg_fetch_assoc($res)) {
        if ($row_del['tipo_descarga'] === 'archivo' && file_exists($row_del['ruta_o_link'])) {
            unlink($row_del['ruta_o_link']);
        }
    }
    pg_query($conexion, "DELETE FROM drivers WHERE id = $id_eliminar");
    header("Location: index.php");
    exit();
}

// Lógica para guardar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_driver'])) {
    $marca = pg_escape_string($conexion, $_POST['marca']);
    $modelo = pg_escape_string($conexion, $_POST['modelo']);
    $sistema_operativo = pg_escape_string($conexion, $_POST['sistema_operativo']);
    $arquitectura = pg_escape_string($conexion, $_POST['arquitectura']);
    $tipo_descarga = $_POST['tipo_descarga'];
    $ruta_o_link = "";

    if ($tipo_descarga === 'archivo' && isset($_FILES['archivo']) && $_FILES['archivo']['error'] === 0) {
        $nombre_archivo = time() . "_" . basename($_FILES['archivo']['name']);
        $destino = "archivos/" . $nombre_archivo;
        if (move_uploaded_file($_FILES['archivo']['tmp_name'], $destino)) {
            $ruta_o_link = $destino;
        }
    } else if ($tipo_descarga === 'enlace') {
        $ruta_o_link = pg_escape_string($conexion, $_POST['enlace_url']);
    }

    if (!empty($ruta_o_link)) {
        $query = "INSERT INTO drivers (marca, modelo, sistema_operativo, arquitectura, tipo_descarga, ruta_o_link) 
                  VALUES ('$marca', '$modelo', '$sistema_operativo', '$arquitectura', '$tipo_descarga', '$ruta_o_link')";
        pg_query($conexion, $query);
    }

    header("Location: index.php");
    exit();
}

$resultado = pg_query($conexion, "SELECT * FROM drivers ORDER BY id DESC");
$total_drivers = pg_num_rows($resultado);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Centro de Control de Drivers</title>
<link rel="icon" href="https://cdn-icons-png.flaticon.com/512/2888/2888708.png" type="image/png">
    <meta name="theme-color" content="#1e293b">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --success: #10b981;
            --danger: #ef4444;
            --border-color: #e2e8f0;
            --radius: 12px;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-main); min-height: 100vh; padding: 24px 16px; }

        .header { max-width: 1200px; margin: 0 auto 24px auto; display: flex; align-items: center; justify-content: space-between; padding-bottom: 16px; border-bottom: 2px solid var(--border-color); }
        .header-title { display: flex; align-items: center; gap: 14px; }
        .header-icon { background: #eff6ff; color: var(--primary); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; }
        .header-title h1 { font-size: 22px; font-weight: 700; color: var(--text-main); }
        .header-title p { font-size: 13px; color: var(--text-muted); }

        .main-container { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 350px 1fr; gap: 24px; }
        @media (max-width: 900px) { .main-container { grid-template-columns: 1fr; } }

        .card { background: var(--card-bg); border-radius: var(--radius); padding: 24px; box-shadow: var(--shadow); border: 1px solid var(--border-color); }
        .card-title { font-size: 18px; font-weight: 600; margin-bottom: 16px; color: var(--text-main); display: flex; align-items: center; gap: 10px; }
        .card-title i { color: var(--primary); }

        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; }
        
        .input-control, select {
            width: 100%; padding: 10px 14px; font-size: 14px; border: 1px solid var(--border-color);
            border-radius: 8px; background-color: #f8fafc; color: var(--text-main); transition: all 0.2s; outline: none;
        }
        .input-control:focus, select:focus { border-color: var(--primary); background-color: #fff; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15); }

        .btn-primary {
            width: 100%; padding: 12px; background: var(--primary); color: white; border: none;
            border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 10px;
        }
        .btn-primary:hover { background: var(--primary-hover); transform: translateY(-1px); }

        .search-wrapper { position: relative; margin-bottom: 20px; }
        .search-wrapper i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 16px; }
        .search-input { padding-left: 42px; font-size: 15px; border-radius: 10px; height: 46px; }

        .stats-badge { display: inline-flex; align-items: center; gap: 6px; background: #eff6ff; color: var(--primary); font-weight: 600; font-size: 13px; padding: 4px 12px; border-radius: 20px; margin-left: auto; }

        .driver-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; }
        .driver-card {
            background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 10px;
            padding: 18px; position: relative; transition: all 0.25s; display: flex; flex-direction: column; justify-content: space-between;
        }
        .driver-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg); border-color: #cbd5e1; }

        .driver-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
        .badge-brand { font-size: 11px; font-weight: 700; text-transform: uppercase; padding: 3px 8px; border-radius: 6px; background: #e2e8f0; color: #334155; }

        .driver-info-main { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .driver-info-main i.device-icon { font-size: 20px; color: var(--primary); background: #f1f5f9; padding: 8px; border-radius: 8px; }

        .driver-model { font-size: 16px; font-weight: 700; color: var(--text-main); }
        .driver-os { font-size: 13px; color: var(--text-muted); margin-bottom: 16px; display: flex; align-items: center; gap: 6px; }

        .card-actions { display: flex; gap: 8px; position: absolute; top: 16px; right: 16px; }
        .action-icon { color: var(--text-muted); font-size: 14px; padding: 4px; transition: color 0.2s; text-decoration: none; }
        .action-icon:hover.edit { color: var(--primary); }
        .action-icon:hover.delete { color: var(--danger); }

        .btn-download {
            display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%;
            padding: 10px; background: #f1f5f9; color: var(--primary); font-weight: 600; font-size: 13px;
            text-decoration: none; border-radius: 8px; transition: all 0.2s;
        }
        .btn-download:hover { background: var(--primary); color: white; }

        .empty-state { text-align: center; padding: 40px 20px; color: var(--text-muted); }
        .empty-state i { font-size: 48px; margin-bottom: 12px; opacity: 0.5; }
    </style>
</head>
<body>

<div class="header">
    <div class="header-title">
        <div class="header-icon">
            <i class="fa-solid fa-print"></i>
        </div>
        <div>
            <h1>Repositorio de Drivers</h1>
            <p>Gestión centralizada de instaladores para PC e Impresoras</p>
        </div>
    </div>
</div>

<div class="main-container">
    <div class="card">
        <div class="card-title">
            <i class="fa-solid fa-plus-circle"></i>
            <span>Registrar Driver</span>
        </div>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Marca de Impresora</label>
                <select name="marca" required>
                    <option value="" disabled selected>Seleccionar Marca</option>
                    <option value="HP">HP</option>
                    <option value="Epson">Epson</option>
                    <option value="Canon">Canon</option>
                    <option value="Kyocera">Kyocera</option>
                    <option value="Brother">Brother</option>
                    <option value="Otra">Otra</option>
                </select>
            </div>

            <div class="form-group">
                <label>Modelo exacto</label>
                <input type="text" name="modelo" class="input-control" placeholder="Ej: LaserJet P1102w" required>
            </div>

            <div class="form-group">
                <label>Sistema Operativo</label>
                <select name="sistema_operativo" required>
                    <option value="Windows 11">Windows 11</option>
                    <option value="Windows 10">Windows 10</option>
                    <option value="Windows 7 / 8">Windows 7 / 8</option>
                    <option value="Linux / Mac">Linux / Mac</option>
                </select>
            </div>

            <div class="form-group">
                <label>Arquitectura</label>
                <select name="arquitectura">
                    <option value="64-bits">64-bits</option>
                    <option value="32-bits">32-bits</option>
                    <option value="Universal">Universal / Ambos</option>
                </select>
            </div>

            <div class="form-group">
                <label>Origen del Instalador</label>
                <select name="tipo_descarga" id="tipo_descarga" onchange="toggleOrigen()">
                    <option value="archivo">Subir Archivo (.exe / .zip)</option>
                    <option value="enlace">Enlace Web Oficial</option>
                </select>
            </div>

            <div class="form-group" id="campo_archivo">
                <input type="file" name="archivo" class="input-control" style="background:white;">
            </div>

            <div class="form-group" id="campo_enlace" style="display:none;">
                <input type="url" name="enlace_url" class="input-control" placeholder="https://sitio-oficial.com/instalador.exe">
            </div>

            <button type="submit" name="guardar_driver" class="btn-primary">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                Guardar en Repositorio
            </button>
        </form>
    </div>

    <div class="card">
        <div class="card-title" style="justify-content: space-between;">
            <div>
                <i class="fa-solid fa-folder-open"></i>
                <span>Drivers Almacenados</span>
            </div>
            <div class="stats-badge">
                <i class="fa-solid fa-database"></i> <?= $total_drivers ?> guardados
            </div>
        </div>

        <div class="search-wrapper">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="buscador" class="input-control search-input" placeholder="Buscar por modelo o marca..." onkeyup="filtrarDrivers()">
        </div>

        <div class="driver-grid" id="lista_drivers">
            <?php if ($total_drivers == 0): ?>
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <i class="fa-solid fa-box-open"></i>
                    <p>No hay drivers registrados aún en el repositorio.</p>
                </div>
            <?php endif; ?>

            <?php while ($row = pg_fetch_assoc($resultado)): ?>
                <div class="driver-card">
                    <div>
                        <div class="driver-header">
                            <span class="badge-brand"><?= htmlspecialchars($row['marca']) ?></span>
                            
                            <div class="card-actions">
                                <a href="editar.php?id=<?= $row['id'] ?>" class="action-icon edit" title="Editar"><i class="fa-solid fa-pen"></i></a>
                                <a href="index.php?eliminar=<?= $row['id'] ?>" class="action-icon delete" title="Eliminar" onclick="return confirm('¿Eliminar este driver?')"><i class="fa-solid fa-trash"></i></a>
                            </div>
                        </div>

                        <div class="driver-info-main">
                            <i class="fa-solid fa-print device-icon"></i>
                            <div>
                                <div class="driver-model"><?= htmlspecialchars($row['modelo']) ?></div>
                            </div>
                        </div>

                        <div class="driver-os">
                            <i class="fa-solid fa-desktop" style="color: var(--primary);"></i>
                            <i class="fa-brands fa-windows"></i>
                            <?= htmlspecialchars($row['sistema_operativo']) ?> (<?= htmlspecialchars($row['arquitectura']) ?>)
                        </div>
                    </div>

                    <a href="<?= htmlspecialchars($row['ruta_o_link']) ?>" class="btn-download" download target="_blank">
                        <i class="fa-solid fa-download"></i>
                        Descargar
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<script>
function toggleOrigen() {
    const tipo = document.getElementById('tipo_descarga').value;
    document.getElementById('campo_archivo').style.display = (tipo === 'archivo') ? 'block' : 'none';
    document.getElementById('campo_enlace').style.display = (tipo === 'enlace') ? 'block' : 'none';
}

function filtrarDrivers() {
    const busqueda = document.getElementById('buscador').value.toLowerCase();
    const tarjetas = document.querySelectorAll('.driver-card');

    tarjetas.forEach(tarjeta => {
        const texto = tarjeta.innerText.toLowerCase();
        tarjeta.style.display = texto.includes(busqueda) ? 'flex' : 'none';
    });
}
</script>

</body>
</html>