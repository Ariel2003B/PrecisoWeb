<?php

// Configuración de la conexión a la base de datos
$host = '127.0.0.1';
$db = 'dbPrecisoGps';
$user = 'precisogps';
$pass = 'Preciso2024!';
$charset = 'utf8mb4';

// $host = '127.0.0.1:3307';
// $db = 'dbPrecisoPruebas';
// $user = 'root';
// $pass = 'Ariel2003B';
// $charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Error en la conexión: " . $e->getMessage());
}

// Función para hashear la contraseña usando bcrypt como en Laravel
function hashPassword($password)
{
    return password_hash($password, PASSWORD_BCRYPT);
}

// Agregar un nuevo usuario
if (isset($_POST['accion']) && $_POST['accion'] === 'agregar') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $cedula = $_POST['cedula'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $per_id = $_POST['per_id'];
    $clave = hashPassword($_POST['clave']); // Hasheamos la contraseña

    $sql = "INSERT INTO USUARIO (PER_ID, NOMBRE, APELLIDO, CEDULA, CORREO, TELEFONO, CLAVE, ESTADO) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'A')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$per_id, $nombre, $apellido, $cedula, $correo, $telefono, $clave]);

    echo "Usuario agregado correctamente.";
}

// Listar todos los usuarios
if (isset($_GET['accion']) && $_GET['accion'] === 'listar') {
    $stmt = $pdo->query("SELECT USU_ID, NOMBRE, APELLIDO, CEDULA, CORREO, TELEFONO FROM USUARIO");
    $usuarios = $stmt->fetchAll();

    echo "<h3>Lista de Usuarios</h3><ul>";
    foreach ($usuarios as $usuario) {
        echo "<li>{$usuario['NOMBRE']} {$usuario['APELLIDO']} - Cédula: {$usuario['CEDULA']} - Correo: {$usuario['CORREO']} - Teléfono: {$usuario['TELEFONO']}</li>";
    }
    echo "</ul>";
}

// Filtrar usuario por cédula
if (isset($_GET['accion']) && $_GET['accion'] === 'filtrar' && isset($_GET['cedula'])) {
    $cedula = $_GET['cedula'];
    $stmt = $pdo->prepare("SELECT USU_ID, NOMBRE, APELLIDO, CEDULA, CORREO, TELEFONO FROM USUARIO WHERE CEDULA = ?");
    $stmt->execute([$cedula]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        echo "<h3>Usuario Encontrado</h3>";
        echo "<p>{$usuario['NOMBRE']} {$usuario['APELLIDO']} - Cédula: {$usuario['CEDULA']} - Correo: {$usuario['CORREO']} - Teléfono: {$usuario['TELEFONO']}</p>";
    } else {
        echo "No se encontró ningún usuario con esa cédula.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestión de Usuarios</title>
</head>
<body>

<h2>Agregar Usuario</h2>
<form method="POST">
    <input type="hidden" name="accion" value="agregar">
    <label>Nombre: <input type="text" name="nombre" required></label><br>
    <label>Apellido: <input type="text" name="apellido" required></label><br>
    <label>Cédula: <input type="text" name="cedula" required></label><br>
    <label>Correo: <input type="email" name="correo" required></label><br>
    <label>Teléfono: <input type="text" name="telefono" required></label><br>
    <label>PER_ID: <input type="number" name="per_id" required></label><br>
    <label>Clave: <input type="password" name="clave" required></label><br>
    <button type="submit">Agregar Usuario</button>
</form>

<h2>Listar Usuarios</h2>
<a href="?accion=listar">Ver Todos los Usuarios</a>

<h2>Filtrar Usuario por Cédula</h2>
<form method="GET">
    <input type="hidden" name="accion" value="filtrar">
    <label>Cédula: <input type="text" name="cedula" required></label>
    <button type="submit">Buscar</button>
</form>

</body>
</html>
