<?php
require 'config.php';

// Obtener productos en el orden configurado
$stmt = $db->query("SELECT * FROM products ORDER BY position ASC, id ASC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cargar imágenes adicionales por producto
$imageStmt = $db->query("SELECT * FROM product_images ORDER BY product_id, position ASC");
$images = $imageStmt->fetchAll(PDO::FETCH_ASSOC);
$imagesByProduct = [];
foreach ($images as $img) {
    $imagesByProduct[$img['product_id']][] = $img;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Productos - Dulcería Quiromar</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        serif: ['"Cormorant Garamond"','serif'],
                        sans: ['Montserrat','sans-serif'],
                        rounded: ['Poppins','ui-sans-serif'],
                        fredoka: ['Fredoka', 'sans-serif']
                    },
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
        .top-accent {
            height: 4px;
            background: linear-gradient(90deg, #00BFFF, #1e3a8a, #00BFFF);
        }
        .header-content h1 {
            font-family: 'Fredoka', sans-serif;
            letter-spacing: 0.05em;
            text-shadow: 3px 3px 0px rgba(0,0,0,0.3), 6px 6px 0px rgba(0,0,0,0.2), 9px 9px 0px rgba(0,0,0,0.1), 12px 12px 20px rgba(0,0,0,0.4);
        }
        /* Producto agotado: imagen en blanco y negro + leve apagado para que se note sin leer la etiqueta */
        .product-image-area--agotado img {
            filter: grayscale(100%);
            opacity: 0.78;
        }
        .product-image-area--agotado::after {
            content: '';
            position: absolute;
            inset: 0;
            z-index: 1;
            pointer-events: none;
            background: linear-gradient(180deg, rgba(0,0,0,0.12) 0%, rgba(0,0,0,0.22) 100%);
            border-radius: 0.75rem 0.75rem 0 0;
        }
        .product-card--agotado {
            opacity: 0.92;
        }
        /* Etiquetas de producto — colores muy vivos estilo dulcería */
        .catalog-tag {
            display: inline-block;
            padding: 0.35rem 0.55rem;
            border-radius: 9999px;
            font-size: 0.625rem;
            font-weight: 900;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            border: 2px solid #fff;
            line-height: 1.15;
            box-shadow:
                0 3px 0 rgba(0, 0, 0, 0.28),
                0 8px 18px rgba(0, 0, 0, 0.35);
        }
        @media (min-width: 480px) {
            .catalog-tag {
                font-size: 0.7rem;
                padding: 0.4rem 0.65rem;
            }
        }
        .catalog-tag--nuevo {
            background: linear-gradient(145deg, #ff5722 0%, #e91e8c 50%, #ff9100 100%);
            color: #fff;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.45);
            box-shadow:
                0 3px 0 #8b1450,
                0 8px 22px rgba(233, 30, 140, 0.65),
                0 0 20px rgba(255, 145, 0, 0.45);
        }
        .catalog-tag--disponible {
            background: linear-gradient(145deg, #00c853 0%, #b2ff59 100%);
            color: #0d260d;
            text-shadow: 0 1px 0 rgba(255, 255, 255, 0.4);
            border-color: #e8f5e9;
            box-shadow:
                0 3px 0 #1b5e20,
                0 8px 22px rgba(0, 200, 83, 0.55),
                0 0 18px rgba(178, 255, 89, 0.4);
        }
        .catalog-tag--agotado {
            background: linear-gradient(145deg, #ff1744 0%, #b71c1c 100%);
            color: #fff;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
            border-color: #ffcdd2;
            box-shadow:
                0 3px 0 #3e0000,
                0 8px 24px rgba(255, 23, 68, 0.6),
                0 0 16px rgba(183, 28, 28, 0.45);
        }
        .catalog-tag--bajas {
            background: linear-gradient(145deg, #ffea00 0%, #ffab00 100%);
            color: #1a1000;
            border-color: #fffde7;
            box-shadow:
                0 3px 0 #b8860b,
                0 8px 22px rgba(255, 171, 0, 0.65),
                0 0 18px rgba(255, 234, 0, 0.5);
        }
        .catalog-tag--precio {
            background: linear-gradient(145deg, #ffd600 0%, #ff6d00 100%);
            color: #1a0800;
            border-color: #fff8e1;
            box-shadow:
                0 3px 0 #bf360c,
                0 8px 22px rgba(255, 109, 0, 0.55),
                0 0 16px rgba(255, 214, 0, 0.45);
        }
        .catalog-tag--vendido {
            background: linear-gradient(145deg, #e040fb 0%, #651fff 100%);
            color: #fff;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.45);
            border-color: #f3e5f5;
            box-shadow:
                0 3px 0 #311b92,
                0 8px 26px rgba(224, 64, 251, 0.55),
                0 0 20px rgba(101, 31, 255, 0.45);
        }
        .catalog-tag--default {
            background: linear-gradient(145deg, #607d8b 0%, #263238 100%);
            color: #fff;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.35);
        }
    </style>
</head>
<body class="font-rounded">
    <!-- Admin Link -->
    <div class="fixed top-4 right-4 z-50">
        <a href="admin/login.php" class="bg-sweet-blue text-white px-4 py-2 rounded-full hover:bg-blue-600 transition duration-300 text-sm shadow-lg">Admin</a>
    </div>

    <div class="top-accent"></div>

    <header class="bg-gradient-to-b from-blue-900 to-blue-800 text-white py-20 text-center relative overflow-hidden border-b-4 border-blue-400" style="background-image: url('assets/imgs/actuality/fondo.webp'); background-size: cover; background-position: center;">
        <div class="header-content max-w-3xl mx-auto px-6 relative z-10">
            <h1 class="text-5xl md:text-6xl font-bold tracking-wide">DULCERÍA QUIROMAR</h1>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-6 py-12">
        <!-- <div class="catalog-intro text-center max-w-2xl mx-auto mb-10">
            <h2 class="text-2xl md:text-3xl font-serif text-blue-900 mb-3" style="font-family: 'Fredoka', sans-serif; font-weight: 600;">Catálogo de Productos</h2>
            <p class="text-gray-600">Ofrecemos dulces y snacks perfectos para complementar la oferta de tu colegio con calidad, variedad y excelentes precios.</p>
        </div> -->

        <div class="product-count text-center mb-8">
            <p class="text-gray-600" style="font-family: 'Fredoka', sans-serif; font-size: 1.1rem;">Mostrando <span id="productCounter" class="font-serif text-lg text-blue-700" style="font-family: 'Fredoka', sans-serif; font-weight: 600;"><?php echo count($products); ?></span> productos disponibles</p>
        </div>

        <form id="order-form">
            <div class="products-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-6" id="productsGrid">
                <?php foreach ($products as $product):
                    $isAgotado = strtoupper(trim($product['tag'] ?? '')) === 'AGOTADO';
                ?>
                    <div class="product-card bg-white rounded-xl shadow-lg overflow-hidden border-2 border-sweet-blue hover:border-dark-blue <?php echo $isAgotado ? 'product-card--agotado' : ''; ?>">
                        <div class="h-80 sm:h-64 bg-white flex items-center justify-center relative overflow-hidden <?php echo $isAgotado ? 'product-image-area--agotado' : ''; ?>">
                            <?php
                                $productImages = $imagesByProduct[$product['id']] ?? [];
                                $firstImage = $productImages[0]['image_path'] ?? $product['image_path'];
                            ?>

                            <?php if (!empty($productImages)): ?>
                                <div class="product-slideshow w-full h-full relative">
                                    <?php foreach ($productImages as $index => $img): ?>
                                        <img src="<?php echo $img['image_path']; ?>" alt="<?php echo $product['name']; ?>" class="slideshow-image absolute inset-0 w-full h-full object-contain rounded-t-xl <?php echo $index === 0 ? 'block' : 'hidden'; ?>">
                                    <?php endforeach; ?>
                                </div>
                            <?php elseif ($firstImage): ?>
                                <img src="<?php echo $firstImage; ?>" alt="<?php echo $product['name']; ?>" class="max-w-full max-h-full object-contain rounded-t-xl">
                            <?php else: ?>
                                <div class="text-center">
                                    <span class="text-4xl">🍬</span>
                                    <p class="text-gray-500 mt-2">Imagen próximamente</p>
                                </div>
                            <?php endif; ?>

                            <div class="catalog-tag absolute top-2 right-2 z-10 <?php
                                $tagClass = [
                                    'NUEVO' => 'catalog-tag--nuevo',
                                    'DISPONIBLE' => 'catalog-tag--disponible',
                                    'AGOTADO' => 'catalog-tag--agotado',
                                    'BAJAS CANTIDADES' => 'catalog-tag--bajas',
                                    'BAJO DE PRECIO' => 'catalog-tag--precio',
                                    'MÁS VENDIDO' => 'catalog-tag--vendido',
                                ];
                                $tagKey = strtoupper(trim($product['tag']));
                                echo $tagClass[$tagKey] ?? 'catalog-tag--default';
                            ?>">
                                <?php echo $product['tag']; ?>
                            </div>
                        </div>
                        <div class="p-3 bg-white">
                            <h3 class="text-lg font-bold text-gray-800 mb-1 text-center"><?php echo $product['name']; ?></h3>
                            <p class="text-2xl font-bold text-dark-blue mb-2 text-center">$<?php echo number_format($product['price'], ($product['price'] == intval($product['price'])) ? 0 : 2, ',', '.'); ?></p>
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-gray-700 font-medium text-sm">Cantidad:</label>
                                <?php if ($isAgotado): ?>
                                    <span class="text-sm font-semibold text-red-600">Sin stock</span>
                                <?php else: ?>
                                    <input type="number" name="quantity[<?php echo $product['id']; ?>]" min="0" value="0" class="quantity-input w-16 border border-gray-300 rounded px-2 py-1 text-center">
                                <?php endif; ?>
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

    <footer class="bg-white py-6 mt-12 border-t">
        <div class="max-w-6xl mx-auto px-6 text-center">
            <p class="text-gray-600">© 2026 Dulcería Quiromar.</p>
        </div>
    </footer>

    <!-- Particles Background -->
    <div id="particles-js"></div>

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

        function initProductSlideshows() {
            const slideshows = document.querySelectorAll('.product-slideshow');
            slideshows.forEach(slideshow => {
                const images = slideshow.querySelectorAll('.slideshow-image');
                if (images.length <= 1) return;
                let current = 0;
                setInterval(() => {
                    images[current].classList.add('hidden');
                    current = (current + 1) % images.length;
                    images[current].classList.remove('hidden');
                }, 2500);
            });
        }

        document.addEventListener('DOMContentLoaded', initProductSlideshows);

        function sendOrder() {
            const form = document.getElementById('order-form');
            const formData = new FormData(form);
            let message = 'Hola Sergio 👋🏽, quiero hacer mi pedido:\n\n';
            let hasItems = false;

            for (let [key, value] of formData.entries()) {
                if (key.startsWith('quantity[') && parseInt(value) > 0) {
                    const productId = key.match(/quantity\[(\d+)\]/)[1];
                    // Aquí necesitaríamos mapear el ID al nombre, pero como es PHP, usamos un array
                    const productName = document.querySelector(`input[name="${key}"]`).closest('.product-card').querySelector('h3').textContent;
                    const price = document.querySelector(`input[name="${key}"]`).closest('.product-card').querySelector('p').textContent.replace('$', '').trim();
                    message += `🍭 ${productName} - Cantidad: ${value} - Precio: $${price}\n`;
                    hasItems = true;
                }
            }

            if (!hasItems) {
                alert('Por favor, selecciona al menos un producto.');
                return;
            }

            message += '\nGracias!';

            const whatsappUrl = `https://wa.me/573133813154?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        }
    </script>
</body>
</html>