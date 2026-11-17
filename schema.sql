-- schema.sql
-- Esquema completo solicitado por el usuario
-- Crea la base de datos y las tablas necesarias para el inventario

CREATE DATABASE IF NOT EXISTS `almacenatec` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `almacenatec`;

-- Desactivar temporalmente las comprobaciones de FK para importaciones ordenadas
SET FOREIGN_KEY_CHECKS = 0;

-- DROP tables if exists (permite reimportar sin errores por existencia previa)
DROP TABLE IF EXISTS movimientos_inventario;
DROP TABLE IF EXISTS detalles_salida;
DROP TABLE IF EXISTS salidas_inventario;
DROP TABLE IF EXISTS detalles_orden_compra;
DROP TABLE IF EXISTS ordenes_compra;
DROP TABLE IF EXISTS productos;
DROP TABLE IF EXISTS categorias_inventario;
DROP TABLE IF EXISTS unidades_medida;
DROP TABLE IF EXISTS jefes_departamento;
DROP TABLE IF EXISTS departamentos;

-- 1. Tabla: departamentos
CREATE TABLE departamentos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    area VARCHAR(100),
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabla: jefes_departamento
CREATE TABLE jefes_departamento (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(150) NOT NULL,
    correo VARCHAR(150) NOT NULL,
    departamento_id INT NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (departamento_id) REFERENCES departamentos(id),
    UNIQUE KEY unique_jefe_activo (departamento_id, activo)
);

-- 3. Tabla: unidades_medida
CREATE TABLE unidades_medida (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    abreviatura VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Tabla: categorias_inventario
CREATE TABLE categorias_inventario (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(150) NOT NULL,
    tipo ENUM('activo', 'consumible') NOT NULL,
    descripcion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. Tabla: productos
CREATE TABLE productos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    codigo_barras VARCHAR(50) UNIQUE,
    descripcion TEXT NOT NULL,
    marca VARCHAR(100),
    modelo VARCHAR(100),
    categoria_id INT NOT NULL,
    unidad_medida_id INT NOT NULL,
    stock_minimo INT DEFAULT 0,
    stock_actual INT DEFAULT 0,
    costo_promedio DECIMAL(15,4) DEFAULT 0.0000,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias_inventario(id),
    FOREIGN KEY (unidad_medida_id) REFERENCES unidades_medida(id),
    INDEX idx_codigo_barras (codigo_barras),
    INDEX idx_categoria (categoria_id)
);

-- 6. Tabla: ordenes_compra (SIMPLIFICADA - proveedor como string)
CREATE TABLE ordenes_compra (
    id INT PRIMARY KEY AUTO_INCREMENT,
    folio VARCHAR(100) NOT NULL UNIQUE,
    proveedor VARCHAR(200) NOT NULL,
    fecha DATE NOT NULL,
    factura_remision VARCHAR(100),
    proyecto VARCHAR(150),
    departamento_id INT NOT NULL,
    jefe_id INT DEFAULT NULL,
    descripcion_general TEXT,
    estatus ENUM('pendiente', 'recibida', 'cancelada') DEFAULT 'pendiente',
    total DECIMAL(15,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (departamento_id) REFERENCES departamentos(id),
    FOREIGN KEY (jefe_id) REFERENCES jefes_departamento(id),
    INDEX idx_fecha (fecha),
    INDEX idx_estatus (estatus)
);

-- 7. Tabla: detalles_orden_compra
CREATE TABLE detalles_orden_compra (
    id INT PRIMARY KEY AUTO_INCREMENT,
    orden_compra_id INT NOT NULL,
    producto_id INT NOT NULL,
    descripcion TEXT NOT NULL,
    cantidad DECIMAL(12,4) NOT NULL,
    unidad_medida_id INT NOT NULL,
    precio_unitario DECIMAL(15,4) NOT NULL,
    importe DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (orden_compra_id) REFERENCES ordenes_compra(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id),
    FOREIGN KEY (unidad_medida_id) REFERENCES unidades_medida(id),
    INDEX idx_orden_compra (orden_compra_id)
);

-- 8. Tabla: salidas_inventario
CREATE TABLE salidas_inventario (
    id INT PRIMARY KEY AUTO_INCREMENT,
    folio VARCHAR(100) NOT NULL UNIQUE,
    fecha DATE NOT NULL,
    proveedor VARCHAR(200),
    factura VARCHAR(100),
    proyecto VARCHAR(150),
    departamento_id INT NOT NULL,
    jefe_id INT DEFAULT NULL,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (departamento_id) REFERENCES departamentos(id),
    FOREIGN KEY (jefe_id) REFERENCES jefes_departamento(id),
    INDEX idx_fecha (fecha)
);

-- 9. Tabla: detalles_salida
CREATE TABLE detalles_salida (
    id INT PRIMARY KEY AUTO_INCREMENT,
    salida_id INT NOT NULL,
    producto_id INT NOT NULL,
    descripcion TEXT NOT NULL,
    cantidad DECIMAL(12,4) NOT NULL,
    unidad_medida_id INT NOT NULL,
    precio_unitario DECIMAL(15,4) NOT NULL,
    importe DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (salida_id) REFERENCES salidas_inventario(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id),
    FOREIGN KEY (unidad_medida_id) REFERENCES unidades_medida(id),
    INDEX idx_salida (salida_id)
);

-- 10. Tabla: movimientos_inventario
CREATE TABLE movimientos_inventario (
    id INT PRIMARY KEY AUTO_INCREMENT,
    producto_id INT NOT NULL,
    tipo_movimiento ENUM('entrada', 'salida', 'ajuste') NOT NULL,
    cantidad DECIMAL(12,4) NOT NULL,
    costo DECIMAL(15,4) NOT NULL,
    referencia_id INT,
    tipo_referencia ENUM('orden_compra', 'salida', 'ajuste') NOT NULL,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (producto_id) REFERENCES productos(id),
    INDEX idx_producto_fecha (producto_id, created_at)
);

-- Reactivar comprobaciones de FK
SET FOREIGN_KEY_CHECKS = 1;

-- Datos semilla (unidades y categorias)
INSERT INTO unidades_medida (nombre, abreviatura) VALUES
    ('Pieza', 'pz'),
    ('Caja', 'caja'),
    ('Lote', 'lote'),
    ('Metro', 'm')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

INSERT INTO categorias_inventario (nombre, tipo, descripcion) VALUES
    ('Electrónica', 'activo', 'Equipo y componentes electrónicos'),
    ('Mobiliario', 'activo', 'Muebles y accesorios'),
    ('Herramienta', 'activo', 'Herramientas y equipos'),
    ('Consumibles', 'consumible', 'Materiales de consumo')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- Asegurar categorías con IDs fijos para lógica de negocio
INSERT INTO categorias_inventario (id, nombre, tipo, descripcion) VALUES
    (2000, 'Consumible (genérico)', 'consumible', 'Categoría base para consumibles'),
    (5000, 'Activo Fijo (genérico)', 'activo', 'Categoría base para activos fijos')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), tipo = VALUES(tipo), descripcion = VALUES(descripcion);

-- Seed departamentos (por defecto)
INSERT INTO departamentos (nombre, area, activo) VALUES
    ('Compras', 'Administración', TRUE),
    ('Almacén', 'Operaciones', TRUE),
    ('Recursos Humanos', 'Administración', TRUE),
    ('TI', 'Sistemas', TRUE)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

