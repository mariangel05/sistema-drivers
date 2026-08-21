<?php
$host = "dpg-da447mm1egvs73b9jqv0-a.oregon-postgres.render.com";
$port = "5432";
$dbname = "sistema_drivers";
$user = "sistema_drivers_user";
$password = "oCFFxzpMgdGAMqvND1Q3RVr42surI4kh";

$connection_string = "host=$host port=$port dbname=$dbname user=$user password=$password";

$conexion = pg_connect($connection_string);

if (!$conexion) {
    die("Error de conexión a la base de datos.");
}
?>
