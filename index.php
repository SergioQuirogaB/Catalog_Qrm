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
    <link rel="stylesheet" href="assets/css/index.css">
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