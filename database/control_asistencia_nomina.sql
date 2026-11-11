-- ==============================================================
-- BASE DE DATOS: control_asistencia_nomina
-- Basado en el diccionario de datos y diagrama relacional
-- ==============================================================

CREATE DATABASE IF NOT EXISTS `control_asistencia_nomina` 
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `control_asistencia_nomina`;

-- --------------------------------------------------------
-- 1. Tabla: departamento
-- --------------------------------------------------------
DROP TABLE IF EXISTS `detalle_nomina`;
DROP TABLE IF EXISTS `bono`;
DROP TABLE IF EXISTS `asistencia`;
DROP TABLE IF EXISTS `usuario`;
DROP TABLE IF EXISTS `empleado`;
DROP TABLE IF EXISTS `concepto_nomina`;
DROP TABLE IF EXISTS `periodo_nomina`;
DROP TABLE IF EXISTS `departamento`;

CREATE TABLE `departamento` (
  `id_departamento` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre_departamento` VARCHAR(100) NOT NULL,
  `descripcion` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id_departamento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. Tabla: empleado
-- --------------------------------------------------------
CREATE TABLE `empleado` (
  `id_empleado` INT(11) NOT NULL AUTO_INCREMENT,
  `cedula` VARCHAR(20) NOT NULL,
  `nombres` VARCHAR(100) NOT NULL,
  `apellidos` VARCHAR(100) NOT NULL,
  `fecha_nacimiento` DATE DEFAULT NULL,
  `fecha_ingreso` DATE NOT NULL,
  `tipo_empleado` ENUM('Administrativo', 'Obrero') NOT NULL,
  `sueldo_base_mensual` DECIMAL(10,2) NOT NULL,
  `id_departamento` INT(11) NOT NULL,
  PRIMARY KEY (`id_empleado`),
  UNIQUE KEY `cedula` (`cedula`),
  KEY `id_departamento` (`id_departamento`),
  CONSTRAINT `fk_empleado_departamento` FOREIGN KEY (`id_departamento`) 
    REFERENCES `departamento` (`id_departamento`) 
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. Tabla: usuario (para el Módulo de Autenticación / Login)
-- --------------------------------------------------------
CREATE TABLE `usuario` (
  `id_usuario` INT(11) NOT NULL AUTO_INCREMENT,
  `id_empleado` INT(11) DEFAULT NULL,
  `usuario` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `rol` ENUM('Administrador', 'Supervisor', 'Empleado') NOT NULL DEFAULT 'Administrador',
  `estado` ENUM('Activo', 'Inactivo') NOT NULL DEFAULT 'Activo',
  `ultimo_login` DATETIME DEFAULT NULL,
  `creado_el` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `uk_usuario` (`usuario`),
  KEY `id_empleado` (`id_empleado`),
  CONSTRAINT `fk_usuario_empleado` FOREIGN KEY (`id_empleado`) 
    REFERENCES `empleado` (`id_empleado`) 
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. Tabla: asistencia
-- --------------------------------------------------------
CREATE TABLE `asistencia` (
  `id_asistencia` INT(11) NOT NULL AUTO_INCREMENT,
  `id_empleado` INT(11) NOT NULL,
  `fecha` DATE NOT NULL,
  `hora_entrada` TIME DEFAULT NULL,
  `hora_salida` TIME DEFAULT NULL,
  `tipo_marcacion` ENUM('Biometrico', 'Tarjeta') NOT NULL,
  `observacion` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id_asistencia`),
  UNIQUE KEY `uk_asistencia_diaria` (`id_empleado`, `fecha`),
  KEY `id_empleado` (`id_empleado`),
  CONSTRAINT `fk_asistencia_empleado` FOREIGN KEY (`id_empleado`) 
    REFERENCES `empleado` (`id_empleado`) 
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 5. Tabla: bono
-- --------------------------------------------------------
CREATE TABLE `bono` (
  `id_bono` INT(11) NOT NULL AUTO_INCREMENT,
  `id_empleado` INT(11) NOT NULL,
  `fecha_otorgamiento` DATE NOT NULL,
  `monto` DECIMAL(10,2) NOT NULL,
  `motivo` VARCHAR(255) DEFAULT NULL,
  `tipo_bono` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`id_bono`),
  KEY `id_empleado` (`id_empleado`),
  CONSTRAINT `fk_bono_empleado` FOREIGN KEY (`id_empleado`) 
    REFERENCES `empleado` (`id_empleado`) 
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 6. Tabla: concepto_nomina
-- --------------------------------------------------------
CREATE TABLE `concepto_nomina` (
  `id_concepto` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre_concepto` VARCHAR(100) NOT NULL,
  `tipo_concepto` ENUM('Asignacion', 'Deduccion') NOT NULL,
  `es_legal` TINYINT(1) DEFAULT 0,
  `formula_calculo` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id_concepto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 7. Tabla: periodo_nomina
-- --------------------------------------------------------
CREATE TABLE `periodo_nomina` (
  `id_periodo` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre_periodo` VARCHAR(50) NOT NULL,
  `fecha_inicio` DATE NOT NULL,
  `fecha_fin` DATE NOT NULL,
  `estado` ENUM('Abierto', 'Cerrado') DEFAULT 'Abierto',
  PRIMARY KEY (`id_periodo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 8. Tabla: detalle_nomina
-- --------------------------------------------------------
CREATE TABLE `detalle_nomina` (
  `id_detalle` INT(11) NOT NULL AUTO_INCREMENT,
  `id_periodo` INT(11) NOT NULL,
  `id_empleado` INT(11) NOT NULL,
  `id_concepto` INT(11) NOT NULL,
  `monto` DECIMAL(10,2) NOT NULL,
  `cantidad_horas` DECIMAL(5,2) DEFAULT NULL,
  PRIMARY KEY (`id_detalle`),
  KEY `id_periodo` (`id_periodo`),
  KEY `id_empleado` (`id_empleado`),
  KEY `id_concepto` (`id_concepto`),
  CONSTRAINT `fk_detalle_periodo` FOREIGN KEY (`id_periodo`) 
    REFERENCES `periodo_nomina` (`id_periodo`) 
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_detalle_empleado` FOREIGN KEY (`id_empleado`) 
    REFERENCES `empleado` (`id_empleado`) 
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_detalle_concepto` FOREIGN KEY (`id_concepto`) 
    REFERENCES `concepto_nomina` (`id_concepto`) 
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==============================================================
-- DATOS DE PRUEBA INICIALES
-- ==============================================================

-- Departamentos
INSERT INTO `departamento` (`id_departamento`, `nombre_departamento`, `descripcion`) VALUES
(1, 'Tecnología e Informática', 'Gestión de infraestructura, desarrollo y soporte de TI'),
(2, 'Recursos Humanos', 'Selección, gestión de personal, bienestar y nómina'),
(3, 'Administración y Finanzas', 'Contabilidad, presupuestos y compras'),
(4, 'Operaciones y Mantenimiento', 'Producción operativa, logística y mantenimiento general');

-- Empleados
INSERT INTO `empleado` (`id_empleado`, `cedula`, `nombres`, `apellidos`, `fecha_nacimiento`, `fecha_ingreso`, `tipo_empleado`, `sueldo_base_mensual`, `id_departamento`) VALUES
(1, 'V-10293847', 'Carlos Eduardo', 'Mendoza Castillo', '1988-04-15', '2020-02-01', 'Administrativo', 850.00, 1),
(2, 'V-18765432', 'Ana María', 'García Salcedo', '1992-09-21', '2021-05-15', 'Administrativo', 720.00, 2),
(3, 'V-23456789', 'Luis Alberto', 'Paredes Rivas', '1995-12-03', '2022-08-10', 'Obrero', 450.00, 4);

-- Usuarios para Login (Contraseña para ambos: admin123)
-- Hash bcrypt generado para 'admin123': $2y$12$TduI.NcyY9deUoS5gXE5h.Sp1WuV2OJhMpdPGoJxOH2IF90iPYN92
INSERT INTO `usuario` (`id_usuario`, `id_empleado`, `usuario`, `password`, `rol`, `estado`) VALUES
(1, 1, 'admin', '$2y$12$TduI.NcyY9deUoS5gXE5h.Sp1WuV2OJhMpdPGoJxOH2IF90iPYN92', 'Administrador', 'Activo'),
(2, 2, 'recursoshumanos', '$2y$12$TduI.NcyY9deUoS5gXE5h.Sp1WuV2OJhMpdPGoJxOH2IF90iPYN92', 'Supervisor', 'Activo');

-- Conceptos de Nómina
INSERT INTO `concepto_nomina` (`id_concepto`, `nombre_concepto`, `tipo_concepto`, `es_legal`, `formula_calculo`) VALUES
(1, 'Sueldo Básico Quincenal', 'Asignacion', 1, 'sueldo_base / 2'),
(2, 'Bono de Transporte', 'Asignacion', 0, 'fijo'),
(3, 'Seguro Social Obligatorio (IVSS)', 'Deduccion', 1, 'sueldo_base * 0.04'),
(4, 'Régimen Prestacional de Vivienda y Hábitat (RPVH)', 'Deduccion', 1, 'sueldo_base * 0.01');

-- Período de Nómina
INSERT INTO `periodo_nomina` (`id_periodo`, `nombre_periodo`, `fecha_inicio`, `fecha_fin`, `estado`) VALUES
(1, '1ra Quincena Noviembre 2026', '2026-11-01', '2026-11-15', 'Abierto');

-- Asistencia de ejemplo
INSERT INTO `asistencia` (`id_asistencia`, `id_empleado`, `fecha`, `hora_entrada`, `hora_salida`, `tipo_marcacion`, `observacion`) VALUES
(1, 1, CURRENT_DATE(), '07:55:00', '16:05:00', 'Biometrico', 'Puntual'),
(2, 2, CURRENT_DATE(), '08:02:00', '16:00:00', 'Tarjeta', 'Sin novedades');
