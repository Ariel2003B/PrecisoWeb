<?php
$servername = "localhost"; // El hostname del servidor MySQL (p.ej., mysql.tudominio.com)
$username = "precisogps";  // Usuario creado en cPanel
$password = "Preciso2024!";  // Contraseña del usuario
$database = "dbPrecisoGps";  // Nombre de tu base de datos

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $database);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
echo "Conexión exitosa a la base de datos";
?>