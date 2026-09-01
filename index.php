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

// Etiquetas disponibles para filtrar en el catálogo
$tagOrder = ['NUEVO', 'DISPONIBLE', 'BAJAS CANTIDADES', 'BAJO DE PRECIO', 'MÁS VENDIDO', 'AGOTADO'];
$availableTags = [];
foreach ($products as $product) {
    $tag = trim((string)($product['tag'] ?? ''));
    if ($tag !== '' && !in_array($tag, $availableTags, true)) {
        $availableTags[] = $tag;
    }
}

$availableTags = array_values(array_filter($availableTags, fn($tag) => in_array(strtoupper($tag), $tagOrder, true)));
$orderedAvailableTags = [];
foreach ($tagOrder as $tagName) {
    foreach ($availableTags as $tagValue) {
        if (strtoupper(trim((string)$tagValue)) === $tagName) {
            $orderedAvailableTags[] = $tagValue;
        }
    }
}
$availableTags = $orderedAvailableTags;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Productos - Dulcería Quiromar</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Ctext y='52' font-size='52'%3E%F0%9F%90%BB%EF%B8%8F%E2%9D%84%3C/text%3E%3C/svg%3E">
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
        :root {
            --page-bg: #f8fbff;
            --page-text: #1f2937;
            --panel-bg: rgba(255,255,255,0.92);
            --panel-border: rgba(14,165,233,0.2);
            --panel-soft: rgba(239,246,255,0.9);
            --card-bg: #ffffff;
            --card-border: rgba(59,130,246,0.28);
            --muted-text: #4b5563;
            --button-sky: #ffffff;
            --button-sky-text: #0284c7;
            --button-admin: #00BFFF;
            --button-admin-text: #ffffff;
            --shadow-soft: rgba(15, 23, 42, 0.12);
        }

        body.theme-dark {
            --page-bg: #07131d;
            --page-text: #e2f3ff;
            --panel-bg: rgba(15, 23, 42, 0.9);
            --panel-border: rgba(125, 211, 252, 0.25);
            --panel-soft: rgba(15, 23, 42, 0.7);
            --card-bg: rgba(15, 23, 42, 0.92);
            --card-border: rgba(125,211,252,0.32);
            --muted-text: #cbd5e1;
            --button-sky: rgba(15, 23, 42, 0.65);
            --button-sky-text: #7dd3fc;
            --button-admin: #0ea5e9;
            --button-admin-text: #e0f2fe;
            --shadow-soft: rgba(14, 165, 233, 0.18);
        }

        body {
            background: var(--page-bg);
            color: var(--page-text);
            transition: background 0.25s ease, color 0.25s ease;
        }

        .floating-tools {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 50;
        }

        .tool-btn {
            width: 3rem;
            height: 3rem;
            border-radius: 9999px;
            border: 2px solid rgba(255,255,255,0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
            box-shadow: 0 10px 25px var(--shadow-soft);
        }

        .tool-btn:hover {
            transform: translateY(-1px) scale(1.02);
        }

        .tool-btn--admin {
            background: var(--button-admin);
            color: var(--button-admin-text);
        }

        .tool-btn--search {
            background: var(--button-sky);
            color: var(--button-sky-text);
            border-color: var(--panel-border);
        }

        .tool-btn--theme {
            background: var(--button-sky);
            color: var(--button-sky-text);
            border-color: var(--panel-border);
        }

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
        .filter-btn {
            border: 2px solid transparent;
            transition: all 0.2s ease;
        }
        .filter-btn.is-active {
            background: linear-gradient(135deg, #00BFFF, #1e3a8a);
            color: white;
            border-color: #1e3a8a;
            box-shadow: 0 8px 20px rgba(0, 191, 255, 0.25);
        }
        .footer-contact a {
            transition: all 0.2s ease;
        }
        .footer-contact a:hover {
            transform: translateY(-1px);
        }
        .whatsapp-float {
            position: fixed;
            right: 1.25rem;
            bottom: 1.25rem;
            width: 2.8rem;
            height: 2.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
            color: white;
            font-size: 1.25rem;
            text-decoration: none;
            box-shadow: 0 12px 28px rgba(18, 140, 126, 0.45), 0 0 0 6px rgba(37, 211, 102, 0.12);
            border: 2px solid rgba(255,255,255,0.8);
            z-index: 60;
            animation: whatsappBlink 1.8s infinite ease-in-out;
        }
        .whatsapp-float::before {
            content: '';
            position: absolute;
            inset: -0.45rem;
            border: 2px solid rgba(37, 211, 102, 0.55);
            border-radius: 9999px;
            animation: whatsappRing 2s infinite ease-out;
        }
        .whatsapp-float:hover {
            transform: translateY(-2px) scale(1.04);
            box-shadow: 0 15px 30px rgba(18, 140, 126, 0.55), 0 0 0 10px rgba(37, 211, 102, 0.18);
        }
        @keyframes whatsappBlink {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
                filter: brightness(1);
            }
            50% {
                transform: scale(1.06);
                opacity: 0.9;
                filter: brightness(1.12);
            }
        }
        @keyframes whatsappRing {
            0% {
                transform: scale(0.9);
                opacity: 1;
            }
            70% {
                transform: scale(1.18);
                opacity: 0;
            }
            100% {
                transform: scale(1.18);
                opacity: 0;
            }
        }
    </style>
</head>
<body class="font-rounded theme-light">
    <div class="floating-tools">
        <button id="searchToggleBtn" type="button" class="tool-btn tool-btn--search" title="Buscar">🔍</button>
        <button id="themeToggleBtn" type="button" class="tool-btn tool-btn--theme" title="Cambiar tema">🌙</button>
    </div>

    <div id="searchPanel" class="fixed top-20 right-4 z-50 hidden w-[min(92vw,420px)]">
        <div class="bg-white/95 backdrop-blur-sm border border-sky-200 rounded-2xl shadow-2xl p-3">
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sky-500 text-lg">🔍</span>
                <input id="productSearchInput" type="text" placeholder="Buscar producto..." class="w-full pl-12 pr-12 py-3 rounded-full border border-sky-200 bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-sky-400 shadow-sm" autocomplete="off">
                <button id="closeSearchBtn" type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 text-xl" aria-label="Cerrar buscador">✕</button>
            </div>
        </div>
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

        <div class="product-count text-center mb-6">
            <p class="text-gray-600" style="font-family: 'Fredoka', sans-serif; font-size: 1.1rem;">Mostrando <span id="productCounter" class="font-serif text-lg text-blue-700" style="font-family: 'Fredoka', sans-serif; font-weight: 600;"><?php echo count($products); ?></span> productos disponibles</p>
        </div>

        <div class="mb-8">
            <div class="hidden sm:flex flex-wrap justify-center gap-3">
                <button type="button" class="filter-btn is-active rounded-full px-4 py-2 text-sm font-semibold bg-white text-dark-blue border border-blue-200 shadow-sm" data-tag="TODOS">Todos</button>
                <?php foreach ($availableTags as $tag): ?>
                    <button type="button" class="filter-btn rounded-full px-4 py-2 text-sm font-semibold bg-white text-gray-700 border border-gray-200 shadow-sm" data-tag="<?php echo strtoupper(trim($tag)); ?>"><?php echo strtoupper(trim($tag)); ?></button>
                <?php endforeach; ?>
            </div>

            <div class="sm:hidden">
                <label for="mobileFilterSelect" class="block text-sm font-semibold text-gray-700 mb-2 text-center">Filtrar por etiqueta</label>
                <select id="mobileFilterSelect" class="w-full max-w-md mx-auto rounded-xl border border-blue-200 bg-white px-4 py-3 text-gray-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-400">
                    <option value="TODOS">Todos</option>
                    <?php foreach ($availableTags as $tag): ?>
                        <option value="<?php echo strtoupper(trim($tag)); ?>"><?php echo strtoupper(trim($tag)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <form id="order-form">
            <div class="products-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-6" id="productsGrid">
                <?php foreach ($products as $product):
                    $isAgotado = strtoupper(trim($product['tag'] ?? '')) === 'AGOTADO';
                    $tagValue = strtoupper(trim((string)($product['tag'] ?? '')));
                ?>
                    <div class="product-card bg-white rounded-xl shadow-lg overflow-hidden border-2 border-sweet-blue hover:border-dark-blue <?php echo $isAgotado ? 'product-card--agotado' : ''; ?>" data-tag="<?php echo $tagValue; ?>" data-name="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>">
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

        <div id="emptyState" class="hidden text-center text-gray-600 text-xl mt-8">
            <p>No hay productos en esta etiqueta por el momento. 🍬</p>
        </div>
    </main>

    <a href="https://wa.me/573133813154?text=Hola%20Dulcer%C3%ADa%20Quiromar%2C%20quiero%20hacer%20un%20pedido" target="_blank" rel="noopener" class="whatsapp-float" aria-label="Chatear por WhatsApp">
        💬
    </a>

    <footer class="bg-gradient-to-r from-slate-900 to-blue-900 text-white py-10 mt-12">
        <div class="max-w-6xl mx-auto px-6 text-center md:text-left md:flex md:items-center md:justify-between gap-6">
            <div>
                <p class="text-xl font-bold mb-2">Dulcería Quiromar</p>
                <p class="text-blue-100">Encuentra tus dulces favoritos y haz tu pedido rápido.</p>
            </div>

            <div class="footer-contact mt-4 md:mt-0 flex flex-col md:items-end gap-2">
                <a href="https://wa.me/573133813154?text=Hola%20Dulcer%C3%ADa%20Quiromar%2C%20quiero%20hacer%20un%20pedido" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 bg-green-500 hover:bg-green-400 text-white px-4 py-2 rounded-full font-semibold shadow-lg">
                    <span>📲</span>
                    <span>313 381 3154</span>
                </a>
                <a href="tel:+573133813154" class="inline-flex items-center justify-center gap-2 text-blue-100 hover:text-white font-medium">
                    <span>📞</span>
                    <span>Llamar ahora</span>
                </a>
                <a href="admin/login.php" class="inline-flex items-center justify-center gap-2 bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-full font-medium border border-white/20 shadow-md transition duration-200">
                    <span>Ingresar</span>
                </a>
            </div>
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

        document.addEventListener('DOMContentLoaded', () => {
            initProductSlideshows();
            const filterButtons = document.querySelectorAll('.filter-btn');
            const mobileFilterSelect = document.getElementById('mobileFilterSelect');
            const productCards = document.querySelectorAll('.product-card');
            const productCounter = document.getElementById('productCounter');
            const emptyState = document.getElementById('emptyState');
            const searchToggleBtn = document.getElementById('searchToggleBtn');
            const searchPanel = document.getElementById('searchPanel');
            const searchInput = document.getElementById('productSearchInput');
            const closeSearchBtn = document.getElementById('closeSearchBtn');
            const themeToggleBtn = document.getElementById('themeToggleBtn');
            let activeTag = 'TODOS';
            let searchQuery = '';

            const savedTheme = localStorage.getItem('quiromar-theme') || 'light';
            document.body.classList.toggle('theme-dark', savedTheme === 'dark');
            document.body.classList.toggle('theme-light', savedTheme !== 'dark');
            if (themeToggleBtn) {
                themeToggleBtn.textContent = savedTheme === 'dark' ? '☀️' : '🌙';
                themeToggleBtn.title = savedTheme === 'dark' ? 'Modo claro' : 'Modo oscuro';
            }

            function applyTheme(theme) {
                const dark = theme === 'dark';
                document.body.classList.toggle('theme-dark', dark);
                document.body.classList.toggle('theme-light', !dark);
                localStorage.setItem('quiromar-theme', theme);
                if (themeToggleBtn) {
                    themeToggleBtn.textContent = dark ? '☀️' : '🌙';
                    themeToggleBtn.title = dark ? 'Modo claro' : 'Modo oscuro';
                }
            }

            function updateFilters(selectedTag = activeTag, query = searchQuery) {
                activeTag = selectedTag;
                searchQuery = query.trim();
                let visibleCount = 0;

                productCards.forEach(card => {
                    const tagMatches = selectedTag === 'TODOS' || (card.dataset.tag || '').toUpperCase() === selectedTag;
                    const name = (card.dataset.name || '').toLowerCase();
                    const queryMatches = !searchQuery || name.includes(searchQuery.toLowerCase());
                    const shouldShow = tagMatches && queryMatches;

                    card.style.display = shouldShow ? '' : 'none';
                    if (shouldShow) visibleCount++;
                });

                productCounter.textContent = visibleCount;
                emptyState.classList.toggle('hidden', visibleCount !== 0);

                filterButtons.forEach(button => {
                    const isActive = button.dataset.tag === selectedTag;
                    button.classList.toggle('is-active', isActive);
                    button.classList.toggle('text-dark-blue', isActive);
                    button.classList.toggle('border-blue-200', isActive);
                    button.classList.toggle('text-gray-700', !isActive);
                    button.classList.toggle('border-gray-200', !isActive);
                });

                if (mobileFilterSelect) {
                    mobileFilterSelect.value = selectedTag;
                }
            }

            filterButtons.forEach(button => {
                button.addEventListener('click', () => {
                    updateFilters(button.dataset.tag || 'TODOS');
                });
            });

            if (mobileFilterSelect) {
                mobileFilterSelect.addEventListener('change', (event) => {
                    updateFilters(event.target.value || 'TODOS');
                });
            }

            if (searchToggleBtn && searchPanel && searchInput && closeSearchBtn) {
                searchToggleBtn.addEventListener('click', () => {
                    const isHidden = searchPanel.classList.contains('hidden');
                    searchPanel.classList.toggle('hidden', !isHidden);
                    if (isHidden) {
                        setTimeout(() => searchInput.focus(), 50);
                    }
                });

                closeSearchBtn.addEventListener('click', () => {
                    searchPanel.classList.add('hidden');
                    searchInput.value = '';
                    updateFilters(activeTag, '');
                });

                searchInput.addEventListener('input', (event) => {
                    updateFilters(activeTag, event.target.value || '');
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        searchPanel.classList.add('hidden');
                        searchInput.value = '';
                        updateFilters(activeTag, '');
                    }
                });

                document.addEventListener('click', (event) => {
                    const clickedInsideSearch = searchPanel.contains(event.target) || searchToggleBtn.contains(event.target);
                    if (!clickedInsideSearch && !searchPanel.classList.contains('hidden')) {
                        searchPanel.classList.add('hidden');
                    }
                });
            }

            if (themeToggleBtn) {
                themeToggleBtn.addEventListener('click', () => {
                    const nextTheme = document.body.classList.contains('theme-dark') ? 'light' : 'dark';
                    applyTheme(nextTheme);
                });
            }

            updateFilters('TODOS', '');
        });

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