<?php
// Reemplaza cada dato con lo que te da Render en tu base de datos
$host = "dpg-xxxxxxxxx-a.oregon-postgres.render.com"; // En Render: External/Internal Hostname
$port = "5432"; 
$dbname = "sistema_drivers"; // En Render: Database
$user = "sistema_drivers_user"; // En Render: Username
$password = "oCFFxzpMgdGAMqvND1..."; // Tu contraseña completa

$connection_string = "host=$host port=$port dbname=$dbname user=$user password=$password";

$conexion = pg_connect($connection_string);

if (!$conexion) {
    die("Error de conexión a la base de datos.");
}
?>
