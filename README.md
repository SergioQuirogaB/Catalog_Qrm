# Catálogo de Productos

Sistema de catálogo de productos en PHP con panel de administración y base de datos MySQL.

## Instalación

1. Asegúrate de tener PHP instalado con soporte para MySQL y PDO.
2. Instala y configura un servidor MySQL (como XAMPP, WAMP o MySQL nativo).
3. Crea la base de datos ejecutando el script `catalog.sql` en MySQL (o déjalo que se cree automáticamente).
4. Coloca los archivos en un servidor web (como Apache) o ejecuta `php -S localhost:8000` en el directorio del proyecto.
5. La base de datos se crea automáticamente al acceder por primera vez (asegúrate de que el usuario MySQL tenga permisos para crear DB).
6. Accede a `index.php` para ver el catálogo.
7. Para administrar, ve a `admin/login.php` para iniciar sesión o `admin/register.php` para registrarte.

## Configuración de MySQL
- Host: localhost
- Usuario: root
- Contraseña: (vacía por defecto, cambia en `config.php` si es necesario)
- Base de datos: catalog (se crea automáticamente)

## Credenciales de admin por defecto:
- Usuario: `admin`
- Contraseña: `admin123`

## Estructura

- `index.php`: Página principal del catálogo.
- `admin/login.php`: Inicio de sesión para admin.
- `admin/register.php`: Registro de nuevos administradores.
- `admin/dashboard.php`: Panel de administración.
- `config.php`: Configuración de la base de datos MySQL.
- `catalog.sql`: Script SQL para MySQL.
- `uploads/`: Carpeta para imágenes de productos.