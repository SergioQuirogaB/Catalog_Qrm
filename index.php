<?php
require 'config.php';

// Obtener productos
$stmt = $db->query("SELECT * FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dulcería QRM - Catálogo de Productos</title>
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
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 191, 255, 0.3);
            transition: all 0.3s ease;
        }
        .product-card {
            transition: all 0.3s ease;
        }
        #particles-js {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -1;
        }
        .quantity-input:focus {
            outline: none;
            border-color: #00BFFF;
            box-shadow: 0 0 0 2px rgba(0, 191, 255, 0.2);
        }
    </style>
</head>
<body class="bg-white min-h-screen relative">
    <!-- Particles Background -->
    <div id="particles-js"></div>

    <!-- Header -->
    <header class="bg-white shadow-lg relative z-10">
        <div class="container mx-auto px-4 py-6 flex justify-between items-center">
            <h1 class="text-4xl font-bold text-dark-blue font-cursive">🐻‍❄️🍭</h1>
            <nav>
                <a href="admin/login.php" class="bg-sweet-blue text-white px-4 py-2 rounded-full hover:bg-blue-600 transition duration-300">Admin</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-12 relative z-10">
        <!-- <h2 class="text-3xl font-semibold text-center text-gray-800 mb-12">¡Descubre nuestros deliciosos productos!</h2> -->
        <form id="order-form">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-6">
                <?php foreach ($products as $product): ?>
                    <div class="product-card bg-white rounded-xl shadow-lg overflow-hidden border-2 border-sweet-blue hover:border-dark-blue">
                        <div class="h-80 sm:h-64 bg-white flex items-center justify-center relative overflow-hidden">
                            <?php if ($product['image_path']): ?>
                                <img src="<?php echo $product['image_path']; ?>" alt="<?php echo $product['name']; ?>" class="max-w-full max-h-full object-contain rounded-t-xl">
                            <?php else: ?>
                                <div class="text-center">
                                    <span class="text-4xl">🍬</span>
                                    <p class="text-gray-500 mt-2">Imagen próximamente</p>
                                </div>
                            <?php endif; ?>
                            <div class="absolute top-2 right-2 <?php 
                                $tagColors = [
                                    'Nuevo' => 'bg-lime-400 text-black',
                                    'Agotado' => 'bg-orange-500 text-white',
                                    'Tendencia' => 'bg-pink-500 text-white',
                                    'Más vendido' => 'bg-cyan-400 text-black',
                                    'Oferta' => 'bg-yellow-400 text-black'
                                ];
                                echo $tagColors[$product['tag']] ?? 'bg-gray-500 text-white';
                            ?> px-2 py-1 rounded-full text-xs font-bold shadow-lg">
                                <?php echo $product['tag']; ?>
                            </div>
                        </div>
                        <div class="p-3 bg-white">
                            <h3 class="text-lg font-bold text-gray-800 mb-1 text-center"><?php echo $product['name']; ?></h3>
                            <p class="text-2xl font-bold text-dark-blue mb-2 text-center">$<?php echo number_format($product['price'], ($product['price'] == intval($product['price'])) ? 0 : 2, ',', '.'); ?></p>
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-gray-700 font-medium text-sm">Cantidad:</label>
                                <input type="number" name="quantity[<?php echo $product['id']; ?>]" min="0" value="0" class="quantity-input w-16 border border-gray-300 rounded px-2 py-1 text-center">
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (!empty($products)): ?>
                <div class="text-center mt-16">
                    <button type="button" onclick="sendOrder()" class="bg-dark-blue text-white px-10 py-5 rounded-full text-xl font-bold hover:bg-blue-900 transition duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                        📱 Enviar Pedido por WhatsApp
                    </button>
                </div>
            <?php endif; ?>
        </form>
        <?php if (empty($products)): ?>
            <div class="text-center text-gray-600 text-xl mt-12">
                <p>¡Pronto tendremos productos deliciosos para ti! 🍭</p>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-white py-6 mt-12 relative z-10 border-t">
        <div class="container mx-auto px-4 text-center">
            <p class="text-gray-600">&copy; 2026 Dulcería Quiromar 🍭</p>
        </div>
    </footer>

    <script>
        particlesJS('particles-js', {
            particles: {
                number: { value: 80, density: { enable: true, value_area: 800 } },
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

        function sendOrder() {
            const form = document.getElementById('order-form');
            const formData = new FormData(form);
            let message = 'Hola, quiero hacer un pedido:\n\n';
            let hasItems = false;

            for (let [key, value] of formData.entries()) {
                if (key.startsWith('quantity[') && parseInt(value) > 0) {
                    const productId = key.match(/quantity\[(\d+)\]/)[1];
                    // Aquí necesitaríamos mapear el ID al nombre, pero como es PHP, usamos un array
                    const productName = document.querySelector(`input[name="${key}"]`).closest('.product-card').querySelector('h3').textContent;
                    const price = document.querySelector(`input[name="${key}"]`).closest('.product-card').querySelector('p').textContent.replace('$', '').trim();
                    message += `${productName} - Cantidad: ${value} - Precio: $${price}\n`;
                    hasItems = true;
                }
            }

            if (!hasItems) {
                alert('Por favor, selecciona al menos un producto.');
                return;
            }

            message += '\nTotal aproximado: (calcula tú)\n\nGracias!';

            const whatsappUrl = `https://wa.me/573133813155?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        }
    </script>
</body>
</html>