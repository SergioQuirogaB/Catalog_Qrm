<?php
session_start();
require '../config.php';

// Verificar si está logueado
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// Manejar logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Manejar agregar producto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $tag = $_POST['tag'];
    $image = $_FILES['image'];

    if ($image['error'] == 0) {
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($image["name"]);
        move_uploaded_file($image["tmp_name"], $target_file);

        $stmt = $db->prepare("INSERT INTO products (name, price, image_path, tag) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $price, $target_file, $tag]);
    }
}

// Manejar eliminar producto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $id = $_POST['delete'];
    $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: dashboard.php');
    exit;
}

// Manejar editar producto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit'])) {
    $id = $_POST['edit_id'];
    $name = $_POST['edit_name'];
    $price = $_POST['edit_price'];
    $tag = $_POST['edit_tag'];
    $image = $_FILES['edit_image'] ?? null;

    $update_fields = "name = ?, price = ?, tag = ?";
    $params = [$name, $price, $tag];

    if ($image && $image['error'] == 0) {
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($image["name"]);
        move_uploaded_file($image["tmp_name"], $target_file);
        $update_fields .= ", image_path = ?";
        $params[] = $target_file;
    }

    $params[] = $id;
    $stmt = $db->prepare("UPDATE products SET $update_fields WHERE id = ?");
    $stmt->execute($params);
    header('Location: dashboard.php');
    exit;
}

// Obtener productos
$stmt = $db->query("SELECT * FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Dulcería QRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'sweet-blue': '#00BFFF',
                        'dark-blue': '#1e3a8a',
                        'light-blue': '#dbeafe',
                    }
                }
            }
        }
    </script>
    <style>
        #particles-js {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -1;
        }
        .dashboard-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 2px solid #00BFFF;
        }
        .input-field:focus {
            outline: none;
            border-color: #00BFFF;
            box-shadow: 0 0 0 2px rgba(0, 191, 255, 0.2);
        }
        .product-item {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid #e5e7eb;
        }
        .product-item:hover {
            border-color: #00BFFF;
        }
    </style>
</head>
<body class="bg-white min-h-screen relative">
    <!-- Particles Background -->
    <div id="particles-js"></div>

    <!-- Header -->
    <header class="bg-white shadow-lg relative z-10">
        <div class="container mx-auto px-4 py-6 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-dark-blue">🐻‍❄️</h1>
            <nav class="flex space-x-4">
                <a href="?logout" class="bg-red-500 text-white px-4 py-2 rounded-full hover:bg-red-600 transition duration-300">Cerrar Sesión</a>
                <a href="../index.php" class="bg-sweet-blue text-white px-4 py-2 rounded-full hover:bg-blue-600 transition duration-300">Ver Catálogo</a>
            </nav>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8 relative z-10">
        <!-- Agregar Producto -->
        <div class="dashboard-card rounded-xl shadow-2xl p-8 mb-8">
            <h2 class="text-2xl font-bold text-dark-blue mb-6 text-center">Agregar Nuevo Producto</h2>
            <form method="post" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Nombre del Producto</label>
                    <input type="text" name="name" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg" placeholder="Ej: Chocolate" required>
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Precio</label>
                    <input type="number" step="0.01" name="price" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg" placeholder="5000" required>
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Etiqueta</label>
                    <select name="tag" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg">
                        <option value="Nuevo">Nuevo</option>
                        <option value="Agotado">Agotado</option>
                        <option value="Tendencia">Tendencia</option>
                        <option value="Más vendido">Más vendido</option>
                        <option value="Oferta">Oferta</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Imagen</label>
                    <input type="file" name="image" accept="image/*" class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-sweet-blue file:text-white hover:file:bg-blue-600" required>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-dark-blue text-white py-3 rounded-lg font-semibold hover:bg-blue-900 transition duration-300 shadow-lg">
                        Agregar Producto
                    </button>
                </div>
            </form>
        </div>

        <!-- Lista de Productos -->
        <div class="dashboard-card rounded-xl shadow-2xl p-8">
            <h2 class="text-2xl font-bold text-dark-blue mb-6 text-center">Productos Actuales</h2>
            <?php if (empty($products)): ?>
                <p class="text-center text-gray-600">No hay productos aún. ¡Agrega el primero!</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full table-auto bg-white rounded-lg shadow-md">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-4 py-2 text-left">Imagen</th>
                                <th class="px-4 py-2 text-left">Nombre</th>
                                <th class="px-4 py-2 text-left">Precio</th>
                                <th class="px-4 py-2 text-left">Etiqueta</th>
                                <th class="px-4 py-2 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-2">
                                        <?php if ($product['image_path']): ?>
                                            <img src="<?php echo $product['image_path']; ?>" alt="<?php echo $product['name']; ?>" class="w-16 h-16 object-cover rounded">
                                        <?php else: ?>
                                            <span class="text-gray-500">Sin imagen</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-2 font-medium"><?php echo $product['name']; ?></td>
                                    <td class="px-4 py-2">$<?php echo number_format($product['price'], ($product['price'] == intval($product['price'])) ? 0 : 2, ',', '.'); ?></td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold 
                                            <?php 
                                            switch($product['tag']) {
                                                case 'Nuevo': echo 'bg-green-100 text-green-800'; break;
                                                case 'Agotado': echo 'bg-red-100 text-red-800'; break;
                                                case 'Tendencia': echo 'bg-purple-100 text-purple-800'; break;
                                                case 'Más vendido': echo 'bg-blue-100 text-blue-800'; break;
                                                case 'Oferta': echo 'bg-yellow-100 text-yellow-800'; break;
                                                default: echo 'bg-gray-100 text-gray-800';
                                            }
                                            ?>">
                                            <?php echo $product['tag']; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <div class="flex justify-center space-x-2">
                                            <button onclick="toggleEdit(<?php echo $product['id']; ?>)" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition duration-300">Editar</button>
                                            <form method="post" class="inline">
                                                <input type="hidden" name="delete" value="<?php echo $product['id']; ?>">
                                                <button type="submit" onclick="return confirm('¿Eliminar este producto?')" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition duration-300">Eliminar</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <tr id="edit-row-<?php echo $product['id']; ?>" class="hidden bg-gray-50">
                                    <td colspan="5" class="px-4 py-4">
                                        <form method="post" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                            <input type="hidden" name="edit_id" value="<?php echo $product['id']; ?>">
                                            <div>
                                                <label class="block text-gray-700 font-medium mb-1">Nombre</label>
                                                <input type="text" name="edit_name" value="<?php echo $product['name']; ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-sweet-blue" required>
                                            </div>
                                            <div>
                                                <label class="block text-gray-700 font-medium mb-1">Precio</label>
                                                <input type="number" step="0.01" name="edit_price" value="<?php echo $product['price']; ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-sweet-blue" required>
                                            </div>
                                            <div>
                                                <label class="block text-gray-700 font-medium mb-1">Etiqueta</label>
                                                <select name="edit_tag" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-sweet-blue">
                                                    <option value="Nuevo" <?php if($product['tag']=='Nuevo') echo 'selected'; ?>>Nuevo</option>
                                                    <option value="Agotado" <?php if($product['tag']=='Agotado') echo 'selected'; ?>>Agotado</option>
                                                    <option value="Tendencia" <?php if($product['tag']=='Tendencia') echo 'selected'; ?>>Tendencia</option>
                                                    <option value="Más vendido" <?php if($product['tag']=='Más vendido') echo 'selected'; ?>>Más vendido</option>
                                                    <option value="Oferta" <?php if($product['tag']=='Oferta') echo 'selected'; ?>>Oferta</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-gray-700 font-medium mb-1">Imagen (opcional)</label>
                                                <input type="file" name="edit_image" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-sweet-blue file:text-white hover:file:bg-blue-600">
                                            </div>
                                            <div class="md:col-span-4 flex justify-end space-x-2 mt-4">
                                                <button type="submit" name="edit" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition duration-300">Guardar Cambios</button>
                                                <button type="button" onclick="toggleEdit(<?php echo $product['id']; ?>)" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition duration-300">Cancelar</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        particlesJS('particles-js', {
            particles: {
                number: { value: 50, density: { enable: true, value_area: 800 } },
                color: { value: '#00BFFF' },
                shape: { type: 'circle' },
                opacity: { value: 0.5, random: true },
                size: { value: 3, random: true },
                line_linked: { enable: true, distance: 150, color: '#00BFFF', opacity: 0.4, width: 1 },
                move: { enable: true, speed: 2, direction: 'none', random: true, straight: false, out_mode: 'out' }
            },
            interactivity: {
                detect_on: 'canvas',
                events: { onhover: { enable: true, mode: 'repulse' }, onclick: { enable: true, mode: 'push' } },
                modes: { repulse: { distance: 100, duration: 0.4 }, push: { particles_nb: 4 } }
            },
            retina_detect: true
        });

        function toggleEdit(id) {
            const row = document.getElementById('edit-row-' + id);
            row.classList.toggle('hidden');
        }
    </script>
</body>
</html>