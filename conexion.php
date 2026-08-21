<?php
$db_url = "postgresql://sistema_drivers_user:oCFfxzpMgdGAMqvNDlQ3RVr42surI4kh@dpg-da447mm1egvs73b9jqv0-a/sistema_drivers";

$conexion = pg_connect($db_url);

if (!$conexion) {
    die("Error de conexión a la base de datos.");
}

// Crea la tabla automáticamente si no existe
$sql_tabla = "CREATE TABLE IF NOT EXISTS drivers (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100),
    cedula VARCHAR(20),
    telefono VARCHAR(20),
    status VARCHAR(20) DEFAULT 'activo'
);";

pg_query($conexion, $sql_tabla);
?>
