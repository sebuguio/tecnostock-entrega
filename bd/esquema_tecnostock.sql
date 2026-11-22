-- Script reproducible para Sistema de Inventario TecnoStock
-- Motor: MySQL / MariaDB (XAMPP)

CREATE DATABASE IF NOT EXISTS tecnostock
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE tecnostock;

-- 1. Entidad Categoría
CREATE TABLE IF NOT EXISTS categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- 2. Entidad Usuario
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(120) NOT NULL UNIQUE,
    clave VARCHAR(255) NOT NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- 3. Entidad Producto
CREATE TABLE IF NOT EXISTS productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(40) NOT NULL UNIQUE,
    nombre VARCHAR(120) NOT NULL,
    descripcion TEXT NULL,
    precio DECIMAL(10,2) NOT NULL CHECK (precio >= 0),
    stock_actual INT NOT NULL DEFAULT 0 CHECK (stock_actual >= 0),
    stock_minimo INT NOT NULL DEFAULT 0 CHECK (stock_minimo >= 0),
    estado TINYINT(1) NOT NULL DEFAULT 1,
    id_categoria INT NOT NULL,
    CONSTRAINT fk_prod_cat
        FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 4. Entidad Movimiento
CREATE TABLE IF NOT EXISTS movimientos (
    id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
    fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tipo ENUM('ENTRADA', 'SALIDA') NOT NULL,
    cantidad INT NOT NULL CHECK (cantidad > 0),
    observacion VARCHAR(255) NULL,
    id_producto INT NOT NULL,
    id_usuario INT NOT NULL,
    CONSTRAINT fk_mov_prod
        FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_mov_user
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Datos iniciales de prueba
-- Nota: Si la tabla usuarios está vacía, acceso.php crea automáticamente
-- admin@tecnostock.cl con contraseña 'tecno123' usando password_hash().

INSERT IGNORE INTO categorias (nombre, descripcion) VALUES
('Periféricos', 'Teclados, mouses y accesorios de escritorio'),
('Almacenamiento', 'Discos SSD, NVMe y unidades portátiles'),
('Conectividad', 'Cables HDMI, DisplayPort, hubs USB-C y adaptadores'),
('Audio', 'Audífonos inalámbricos, micrófonos y parlantes');

INSERT IGNORE INTO productos (codigo, nombre, descripcion, precio, stock_actual, stock_minimo, id_categoria) VALUES
('PER-101', 'Mouse Óptico Ergonómico', 'Sensor de alta precisión 6 botones USB', 22990, 14, 5, 1),
('ALM-201', 'Unidad SSD NVMe 1TB', 'Lectura secuencial rápida PCIe Gen4', 58990, 2, 6, 2),
('CON-301', 'Hub USB-C 7 en 1 Aluminio', 'Salida HDMI 4K, lector SD y carga PD', 18990, 9, 4, 3),
('AUD-401', 'Audífonos In-Ear Cancelación Activa', 'Perfil de sonido balanceado Bluetooth 5.3', 44990, 3, 5, 4);
