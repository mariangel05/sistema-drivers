<?php
$db_url = "postgresql://sistema_drivers_user:oCFfxzpMgdGAMqvNDlQ3RVr42surI4kh@dpg-da447mm1egvs73b9jqv0-a/sistema_drivers";

$conexion = pg_connect($db_url);

if (!$conexion) {
    die("Error de conexión a la base de datos.");
}

// Borrar la tabla anterior si está mal estructurada y crearla limpia
pg_query($conexion, "DROP TABLE IF EXISTS drivers;");

$sql_tabla = "CREATE TABLE drivers (
    id SERIAL PRIMARY KEY,
    marca VARCHAR(100),
    modelo VARCHAR(100),
    sistema VARCHAR(50),
    arquitectura VARCHAR(20),
    enlace_terabox TEXT
);";

pg_query($conexion, $sql_tabla);
?>
