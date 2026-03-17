<?php
// config.php - Configuración de la base de datos MySQL
$host = 'localhost';
$dbname = 'catalogo_qrm';
$user = 'root';
$password = ''; // Cambia si tienes contraseña

try {
    // Conectar sin DB para crear si no existe
    $db_temp = new PDO("mysql:host=$host;charset=utf8", $user, $password);
    $db_temp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db_temp->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
} catch (PDOException $e) {
    die("Error al crear la base de datos: " . $e->getMessage());
}

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Verificar si las tablas existen, si no, crearlas
$result = $db->query("SHOW TABLES LIKE 'users'");
if ($result->rowCount() == 0) {
    // Ejecutar el script SQL
    $sql = file_get_contents('catalog.sql');
    $db->exec($sql);
}
?>