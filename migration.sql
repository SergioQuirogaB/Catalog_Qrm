-- Script de migración para actualizar la base de datos
-- Ejecutar este script en MySQL para agregar la columna 'tag' a la tabla products

USE catalogo_qrm;

-- Agregar columna tag si no existe
ALTER TABLE products ADD COLUMN IF NOT EXISTS tag VARCHAR(50) DEFAULT 'Nuevo';

-- Actualizar productos existentes con 'Nuevo' si no tienen tag
UPDATE products SET tag = 'Nuevo' WHERE tag IS NULL OR tag = '';

-- Opcional: Insertar algunos productos de ejemplo con diferentes tags
-- INSERT INTO products (name, price, image_path, tag) VALUES
-- ('Chocolate Premium', 15000, 'uploads/chocolate.jpg', 'Más vendido'),
-- ('Caramelo Artesanal', 8000, 'uploads/caramelo.jpg', 'Tendencia'),
-- ('Galleta Especial', 12000, 'uploads/galleta.jpg', 'Oferta');