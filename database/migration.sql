-- Script de migración para actualizar la base de datos
-- Ejecutar este script en MySQL para agregar la columna 'tag' a la tabla products

USE catalogo_qrm;

-- Agregar columna tag si no existe
ALTER TABLE products ADD COLUMN IF NOT EXISTS tag VARCHAR(50) DEFAULT 'Nuevo';

-- Actualizar productos existentes con 'Nuevo' si no tienen tag
UPDATE products SET tag = 'Nuevo' WHERE tag IS NULL OR tag = '';

-- Agregar columna position para ordenar productos
ALTER TABLE products ADD COLUMN IF NOT EXISTS position INT DEFAULT 0;

-- Inicializar posición para productos existentes (en base a id)
UPDATE products SET position = id WHERE position = 0;

-- Crear tabla de imágenes múltiples por producto
CREATE TABLE IF NOT EXISTS product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    position INT DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Opcional: Insertar algunos productos de ejemplo con diferentes tags
-- INSERT INTO products (name, price, image_path, tag) VALUES
-- ('Chocolate Premium', 15000, 'uploads/chocolate.jpg', 'Más vendido'),
-- ('Caramelo Artesanal', 8000, 'uploads/caramelo.jpg', 'Tendencia'),
-- ('Galleta Especial', 12000, 'uploads/galleta.jpg', 'Oferta');