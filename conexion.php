<?php
// Pega la URL copiada directamente dentro de las comillas
$connection_string = "postgresql://sistema_drivers_user:oCFFxzpMgdGAMqvND1..."; 

$conexion = pg_connect($connection_string);

if (!$conexion) {
    die("Error de conexión a la base de datos.");
}
?>