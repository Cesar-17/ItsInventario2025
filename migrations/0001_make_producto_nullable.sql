-- Migration: make producto_id nullable in detalles_orden_compra and detalles_salida
-- Run this on your MySQL server for the 'almacenatec' database (phpMyAdmin or mysql CLI).

ALTER TABLE detalles_orden_compra
    MODIFY COLUMN producto_id INT NULL;

ALTER TABLE detalles_salida
    MODIFY COLUMN producto_id INT NULL;

-- If there are foreign key constraints that enforce NOT NULL, the above will keep the FK but allow NULL values.
-- After running this, the application can insert detalles without selecting a product.
