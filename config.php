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

// Ejecutar script de migraciones (agrega columnas/tablas nuevas sin afectar datos existentes)
if (file_exists('migration.sql')) {
    try {
        $migrationSql = file_get_contents('migration.sql');
        $db->exec($migrationSql);
    } catch (PDOException $e) {
        // No detener la aplicación si la migración ya está aplicada o falla por permisos.
    }
}

// Asegurar que las columnas/tablas mínimas existan (compatibilidad con MySQL antiguas)
try {
    $colRes = $db->query("SHOW COLUMNS FROM products LIKE 'position'");
    if ($colRes && $colRes->rowCount() === 0) {
        $db->exec("ALTER TABLE products ADD COLUMN position INT DEFAULT 0");
        $db->exec("UPDATE products SET position = id WHERE position = 0");
    }

    $tblRes = $db->query("SHOW TABLES LIKE 'product_images'");
    if ($tblRes && $tblRes->rowCount() === 0) {
        $db->exec("CREATE TABLE product_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            image_path VARCHAR(255) NOT NULL,
            position INT DEFAULT 0,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )");
    }
} catch (PDOException $e) {
    // Ignorar errores de migración, el app seguirá funcionando con la estructura actual.
}
?>