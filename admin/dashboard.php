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

// Manejar ordenar productos (drag & drop)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order'])) {
    $order = explode(',', $_POST['order']);
    $stmt = $db->prepare("UPDATE products SET position = ? WHERE id = ?");

    foreach ($order as $index => $productId) {
        $stmt->execute([$index + 1, $productId]);
    }

    header('Location: dashboard.php');
    exit;
}

// Manejar agregar producto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['name']) && isset($_FILES['images'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $tag = $_POST['tag'];

    // Determinar la siguiente posición
    $posStmt = $db->query("SELECT COALESCE(MAX(position), 0) + 1 FROM products");
    $position = $posStmt->fetchColumn();

    $stmt = $db->prepare("INSERT INTO products (name, price, tag, position) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $price, $tag, $position]);
    $productId = $db->lastInsertId();

    // Guardar imágenes múltiples
    $target_dir = "../uploads/";
    $files = $_FILES['images'];
    $imageStmt = $db->prepare("INSERT INTO product_images (product_id, image_path, position) VALUES (?, ?, ?)");
    $posStmt = $db->prepare("SELECT COALESCE(MAX(position), 0) + 1 FROM product_images WHERE product_id = ?");

    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK) {
            $filename = uniqid() . '_' . basename($files['name'][$i]);
            $target_file = $target_dir . $filename;
            move_uploaded_file($files['tmp_name'][$i], $target_file);

            $posStmt->execute([$productId]);
            $imgPos = $posStmt->fetchColumn();
            $imageStmt->execute([$productId, $target_file, $imgPos]);
        }
    }

    header('Location: dashboard.php');
    exit;
}

// Manejar eliminar producto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $id = $_POST['delete'];

    // Eliminar imágenes asociadas (archivos + DB)
    $imgStmt = $db->prepare("SELECT image_path FROM product_images WHERE product_id = ?");
    $imgStmt->execute([$id]);
    foreach ($imgStmt->fetchAll(PDO::FETCH_COLUMN) as $imgPath) {
        if (file_exists($imgPath)) {
            @unlink($imgPath);
        }
    }

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

    $update_fields = "name = ?, price = ?, tag = ?";
    $params = [$name, $price, $tag];

    // Eliminar imágenes marcadas para borrar
    if (!empty($_POST['delete_images']) && is_array($_POST['delete_images'])) {
        $deleteStmt = $db->prepare("SELECT image_path FROM product_images WHERE id = ? AND product_id = ?");
        $delImgStmt = $db->prepare("DELETE FROM product_images WHERE id = ? AND product_id = ?");
        foreach ($_POST['delete_images'] as $imgId) {
            $deleteStmt->execute([$imgId, $id]);
            $imgPath = $deleteStmt->fetchColumn();
            if ($imgPath && file_exists($imgPath)) {
                @unlink($imgPath);
            }
            $delImgStmt->execute([$imgId, $id]);
        }
    }

    // Agregar nuevas imágenes si se cargaron
    if (!empty($_FILES['edit_images']) && isset($_FILES['edit_images']['name'])) {
        $files = $_FILES['edit_images'];
        $target_dir = "../uploads/";
        $posStmt = $db->prepare("SELECT COALESCE(MAX(position), 0) + 1 FROM product_images WHERE product_id = ?");
        $imageInsert = $db->prepare("INSERT INTO product_images (product_id, image_path, position) VALUES (?, ?, ?)");

        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $filename = uniqid() . '_' . basename($files['name'][$i]);
                $target_file = $target_dir . $filename;
                move_uploaded_file($files['tmp_name'][$i], $target_file);

                $posStmt->execute([$id]);
                $imgPos = $posStmt->fetchColumn();
                $imageInsert->execute([$id, $target_file, $imgPos]);
            }
        }
    }

    $params[] = $id;
    $stmt = $db->prepare("UPDATE products SET $update_fields WHERE id = ?");
    $stmt->execute($params);
    header('Location: dashboard.php');
    exit;
}

// Obtener productos (ordenados por posición)
$stmt = $db->query("SELECT * FROM products ORDER BY position ASC, id ASC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cargar imágenes asociadas a los productos
$imageStmt = $db->query("SELECT * FROM product_images ORDER BY product_id, position ASC");
$images = $imageStmt->fetchAll(PDO::FETCH_ASSOC);
$imagesByProduct = [];
foreach ($images as $image) {
    $imagesByProduct[$image['product_id']][] = $image;
}
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
                    <label class="block text-gray-700 font-medium mb-2">Imágenes (puedes subir varias)</label>
                    <input type="file" name="images[]" accept="image/*" multiple class="input-field w-full px-4 py-3 border border-gray-300 rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-sweet-blue file:text-white hover:file:bg-blue-600" required>
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
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
                <p class="text-sm text-gray-600">Arrastra y suelta los productos para cambiar el orden en que aparecen en el catálogo.</p>
                <form id="order-form" method="post" class="flex items-center gap-2">
                    <input type="hidden" name="order" id="order-input">
                    <button type="submit" class="bg-sweet-blue text-white px-4 py-2 rounded-full hover:bg-blue-600 transition duration-200 shadow">
                        Guardar orden
                    </button>
                </form>
            </div>
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
                                <tr class="border-b hover:bg-gray-50 draggable-row" draggable="true" data-product-id="<?php echo $product['id']; ?>" id="product-row-<?php echo $product['id']; ?>">
                                    <?php
                                        $imgs = $imagesByProduct[$product['id']] ?? [];
                                        $firstImg = $imgs[0]['image_path'] ?? $product['image_path'];
                                    ?>
                                    <td class="px-4 py-2">
                                        <?php if ($firstImg): ?>
                                            <img src="<?php echo $firstImg; ?>" alt="<?php echo $product['name']; ?>" class="w-16 h-16 object-cover rounded">
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
                                            <?php $editImages = $imagesByProduct[$product['id']] ?? []; ?>
                                            <?php if (!empty($editImages)): ?>
                                                <div class="md:col-span-4">
                                                    <label class="block text-gray-700 font-medium mb-1">Imágenes actuales (marca para eliminar)</label>
                                                    <div class="flex flex-wrap gap-2">
                                                        <?php foreach ($editImages as $img): ?>
                                                            <div class="w-20 h-20 relative border rounded overflow-hidden">
                                                                <img src="<?php echo $img['image_path']; ?>" class="w-full h-full object-cover">
                                                                <label class="absolute top-1 right-1 bg-white/80 rounded px-1 text-xs flex items-center gap-1">
                                                                    <input type="checkbox" name="delete_images[]" value="<?php echo $img['id']; ?>">
                                                                    Eliminar
                                                                </label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <label class="block text-gray-700 font-medium mb-1">Agregar nuevas imágenes</label>
                                                <input type="file" name="edit_images[]" accept="image/*" multiple class="w-full px-3 py-2 border border-gray-300 rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-sweet-blue file:text-white hover:file:bg-blue-600">
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

        function updateOrderInput() {
            const orderInput = document.getElementById('order-input');
            const rows = document.querySelectorAll('tr.draggable-row');
            const order = Array.from(rows).map(row => row.dataset.productId);
            orderInput.value = order.join(',');
        }

        function setupDragOrder() {
            const rows = document.querySelectorAll('tr.draggable-row');
            let draggedRow = null;

            rows.forEach(row => {
                row.addEventListener('dragstart', (event) => {
                    draggedRow = row;
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', row.dataset.productId);
                    row.classList.add('opacity-50');
                });

                row.addEventListener('dragend', () => {
                    if (draggedRow) {
                        draggedRow.classList.remove('opacity-50');
                        draggedRow = null;
                    }
                });

                row.addEventListener('dragover', (event) => {
                    event.preventDefault();
                    row.classList.add('bg-blue-50');
                });

                row.addEventListener('dragleave', () => {
                    row.classList.remove('bg-blue-50');
                });

                row.addEventListener('drop', (event) => {
                    event.preventDefault();
                    row.classList.remove('bg-blue-50');

                    if (!draggedRow || draggedRow === row) return;

                    const tbody = row.parentElement;
                    tbody.insertBefore(draggedRow, row);

                    updateOrderInput();
                });
            });

            updateOrderInput();
        }

        document.addEventListener('DOMContentLoaded', () => {
            setupDragOrder();
        });

        function toggleEdit(id) {
            const row = document.getElementById('edit-row-' + id);
            row.classList.toggle('hidden');
        }
    </script>
</body>
</html>