-- Script de creación de base de datos LinkViewer
-- Estándar FORJIATO

-- CREATE DATABASE IF NOT EXISTS linkviewer CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE linkviewer;

-- Tabla de Usuarios (Cada usuario es un tenant)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'usuario') DEFAULT 'usuario',
    suscripcion ENUM('gratis', 'pro', 'premium') DEFAULT 'gratis',
    id_tenant INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (id_tenant)
) ENGINE=InnoDB;

-- Tabla de Categorías
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_tenant INT NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    color VARCHAR(7) DEFAULT '#3498db',
    icono VARCHAR(50) DEFAULT 'fa-folder',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_tenant) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX (id_tenant)
) ENGINE=InnoDB;

-- Tabla de Enlaces
CREATE TABLE IF NOT EXISTS enlaces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_tenant INT NOT NULL,
    id_categoria INT,
    url TEXT NOT NULL,
    titulo VARCHAR(255),
    descripcion TEXT,
    imagen_url TEXT,
    notas TEXT,
    es_favorito BOOLEAN DEFAULT 0,
    ver_mas_tarde BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_tenant) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_categoria) REFERENCES categorias(id) ON DELETE SET NULL,
    INDEX (id_tenant),
    INDEX (es_favorito),
    INDEX (ver_mas_tarde)
) ENGINE=InnoDB;

-- Tabla de Logs de Actividad (Seguridad FORJIATO)
CREATE TABLE IF NOT EXISTS logs_actividad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_tenant INT,
    accion VARCHAR(100) NOT NULL,
    detalles TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (id_tenant)
) ENGINE=InnoDB;

-- Tabla de Contactos (Red Interna)
CREATE TABLE IF NOT EXISTS contactos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_solicitante INT NOT NULL,
    id_receptor INT NOT NULL,
    estado ENUM('pendiente', 'aceptado', 'rechazado') DEFAULT 'pendiente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_solicitante) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_receptor) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_relacion (id_solicitante, id_receptor)
) ENGINE=InnoDB;

-- Tabla de Enlaces Compartidos
CREATE TABLE IF NOT EXISTS enlaces_compartidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_enlace INT NOT NULL,
    id_emisor INT NOT NULL,
    id_receptor INT NOT NULL,
    visto BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_enlace) REFERENCES enlaces(id) ON DELETE CASCADE,
    FOREIGN KEY (id_emisor) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_receptor) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabla de Configuración Global
CREATE TABLE IF NOT EXISTS configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    c_key VARCHAR(100) UNIQUE NOT NULL,
    c_value TEXT,
    c_label VARCHAR(150)
) ENGINE=InnoDB;

INSERT IGNORE INTO configuracion (c_key, c_value, c_label) VALUES 
('app_name', 'Montia', 'Nombre de la Aplicación'),
('allow_registrations', '1', 'Permitir nuevos registros'),
('maintenance_mode', '0', 'Modo Mantenimiento'),
('max_links_free', '50', 'Límite de links para usuarios gratis');

-- Tabla de Solicitudes de Cambio de Plan
CREATE TABLE IF NOT EXISTS solicitudes_plan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    plan_solicitado VARCHAR(50) NOT NULL,
    estado ENUM('pendiente', 'completado', 'cancelado') DEFAULT 'pendiente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Poblar categorías iniciales por defecto para nuevos usuarios
-- Tecnología, Gatitos, Risa, Trabajo, Educación, Comida, Viajes.
