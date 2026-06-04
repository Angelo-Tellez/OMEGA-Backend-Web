-- ============================================================
-- Company    : OMEGA Solutions (OMEGA)
-- Project    : ATN - Sistema de Control de Asistencias
-- File       : create_database_ATN.sql
-- Created on : 03/06/2026
-- Created by : Angelo Armando Tellez Enriquez
-- Reviewed by:
-- ------------------------------------------------------------
-- Changelog:
--   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del script
-- ------------------------------------------------------------
-- USO:
--   mysql -u root -p < create_database_ATN.sql
--   O pegarlo directamente en MySQL Workbench / phpMyAdmin
-- ============================================================

-- ─── CREAR Y SELECCIONAR LA BASE DE DATOS ────────────────────────────────────
CREATE DATABASE IF NOT EXISTS control_asistencias_bd_dev
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE control_asistencias_bd_dev;

SET FOREIGN_KEY_CHECKS = 0;

-- ─── 01. USUARIOS ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id_usuario`  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre`      VARCHAR(100)    NOT NULL,
    `ap_pat`      VARCHAR(100)    NOT NULL,
    `ap_mat`      VARCHAR(100)    NOT NULL,
    `email`       VARCHAR(200)    NOT NULL,
    `contrasenia` VARCHAR(255)    NOT NULL,
    `rol`         TINYINT UNSIGNED NOT NULL COMMENT '1=Docente, 2=Alumno',
    `created_at`  TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`  TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id_usuario`),
    UNIQUE KEY `usuarios_email_unique` (`email`),
    CONSTRAINT `chk_rol` CHECK (`rol` IN (1, 2))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 02. INSTITUCIONES ────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `instituciones` (
    `id_institucion` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_docente`     BIGINT UNSIGNED NOT NULL,
    `nombre`         VARCHAR(150)    NOT NULL,
    `logo`           VARCHAR(500)    NOT NULL,
    `created_at`     TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`     TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id_institucion`),
    KEY `instituciones_id_docente_foreign` (`id_docente`),
    CONSTRAINT `instituciones_id_docente_foreign`
        FOREIGN KEY (`id_docente`) REFERENCES `usuarios` (`id_usuario`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 03. RUBROS DE EVALUACIÓN ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `rubros_evaluacion` (
    `id_rubro`          BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `id_institucion`    BIGINT UNSIGNED  NOT NULL,
    `nombre`            VARCHAR(100)     NOT NULL,
    `porcentaje_minimo` DECIMAL(5,2)     NOT NULL,
    `created_at`        TIMESTAMP        NULL DEFAULT NULL,
    `updated_at`        TIMESTAMP        NULL DEFAULT NULL,
    PRIMARY KEY (`id_rubro`),
    KEY `rubros_evaluacion_id_institucion_foreign` (`id_institucion`),
    CONSTRAINT `rubros_evaluacion_id_institucion_foreign`
        FOREIGN KEY (`id_institucion`) REFERENCES `instituciones` (`id_institucion`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `chk_porcentaje` CHECK (`porcentaje_minimo` BETWEEN 0 AND 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 04. PERIODOS ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `periodos` (
    `id_periodo`     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_institucion` BIGINT UNSIGNED NOT NULL,
    `nombre`         VARCHAR(100)    NOT NULL COMMENT 'Ej: Enero Junio 2026',
    `activo`         TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`     TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`     TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id_periodo`),
    KEY `periodos_id_institucion_foreign` (`id_institucion`),
    CONSTRAINT `periodos_id_institucion_foreign`
        FOREIGN KEY (`id_institucion`) REFERENCES `instituciones` (`id_institucion`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 05. GRUPOS ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `grupos` (
    `id_grupo`       BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `id_institucion` BIGINT UNSIGNED  NOT NULL,
    `id_docente`     BIGINT UNSIGNED  NOT NULL,
    `nombre`         VARCHAR(100)     NOT NULL,
    `materia`        VARCHAR(150)     NOT NULL,
    `periodo`        VARCHAR(50)      NOT NULL,
    `horario`        JSON             NULL     COMMENT '[{"dia":"L","hora_inicio":"07:00","hora_fin":"09:00"}, ...]',
    `no_alumnos`     INT UNSIGNED     NOT NULL,
    `codigo_inv`     VARCHAR(20)      NULL     DEFAULT NULL,
    `created_at`     TIMESTAMP        NULL DEFAULT NULL,
    `updated_at`     TIMESTAMP        NULL DEFAULT NULL,
    PRIMARY KEY (`id_grupo`),
    UNIQUE KEY `grupos_codigo_inv_unique` (`codigo_inv`),
    KEY `grupos_id_institucion_foreign` (`id_institucion`),
    KEY `grupos_id_docente_foreign` (`id_docente`),
    CONSTRAINT `grupos_id_institucion_foreign`
        FOREIGN KEY (`id_institucion`) REFERENCES `instituciones` (`id_institucion`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `grupos_id_docente_foreign`
        FOREIGN KEY (`id_docente`) REFERENCES `usuarios` (`id_usuario`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `chk_no_alumnos` CHECK (`no_alumnos` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 06. GRUPO_ALUMNOS ────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `grupo_alumnos` (
    `id_grupo_alumno` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_grupo`        BIGINT UNSIGNED NOT NULL,
    `id_alumno`       BIGINT UNSIGNED NOT NULL,
    `fec_inscripcion` DATE            NOT NULL DEFAULT (CURRENT_DATE),
    `created_at`      TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`      TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id_grupo_alumno`),
    KEY `grupo_alumnos_id_grupo_foreign` (`id_grupo`),
    KEY `grupo_alumnos_id_alumno_foreign` (`id_alumno`),
    CONSTRAINT `grupo_alumnos_id_grupo_foreign`
        FOREIGN KEY (`id_grupo`) REFERENCES `grupos` (`id_grupo`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `grupo_alumnos_id_alumno_foreign`
        FOREIGN KEY (`id_alumno`) REFERENCES `usuarios` (`id_usuario`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 07. SESIONES ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `sesiones` (
    `id_sesion`     BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `id_grupo`      BIGINT UNSIGNED  NOT NULL,
    `clave`         VARCHAR(20)      NULL     DEFAULT NULL COMMENT 'NULL al cerrar la sesion (RF-64)',
    `est_sesion`    TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '1=Activa, 0=Cerrada',
    `fec_sesion`    DATE             NOT NULL,
    `hora_apertura` DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `hora_cierre`   DATETIME         NULL     DEFAULT NULL,
    `created_at`    TIMESTAMP        NULL DEFAULT NULL,
    `updated_at`    TIMESTAMP        NULL DEFAULT NULL,
    PRIMARY KEY (`id_sesion`),
    KEY `sesiones_id_grupo_foreign` (`id_grupo`),
    CONSTRAINT `sesiones_id_grupo_foreign`
        FOREIGN KEY (`id_grupo`) REFERENCES `grupos` (`id_grupo`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `chk_est_sesion` CHECK (`est_sesion` IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 08. ASISTENCIAS ──────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `asistencias` (
    `id_asistencia`  BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `id_sesion`      BIGINT UNSIGNED  NOT NULL,
    `id_alumno`      BIGINT UNSIGNED  NOT NULL,
    `est_asistencia` TINYINT UNSIGNED NOT NULL COMMENT '1=Presente, 2=Ausente, 3=Justificada',
    `hora_registro`  DATETIME         NULL     DEFAULT NULL,
    `created_at`     TIMESTAMP        NULL DEFAULT NULL,
    `updated_at`     TIMESTAMP        NULL DEFAULT NULL,
    PRIMARY KEY (`id_asistencia`),
    UNIQUE KEY `asistencias_id_sesion_id_alumno_unique` (`id_sesion`, `id_alumno`),
    KEY `asistencias_id_alumno_foreign` (`id_alumno`),
    CONSTRAINT `asistencias_id_sesion_foreign`
        FOREIGN KEY (`id_sesion`) REFERENCES `sesiones` (`id_sesion`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `asistencias_id_alumno_foreign`
        FOREIGN KEY (`id_alumno`) REFERENCES `usuarios` (`id_usuario`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `chk_est_asistencia` CHECK (`est_asistencia` IN (1, 2, 3))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 09. SUSCRIPCIONES ────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `suscripciones` (
    `id_suscripcion`  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_usuario`      BIGINT UNSIGNED NOT NULL,
    `plan`            TINYINT         NOT NULL DEFAULT 0 COMMENT '0=basico, 1=mensual',
    `est_suscripcion` TINYINT         NOT NULL DEFAULT 1 COMMENT '0=inactivo, 1=activo, 2=vencido, 3=gracia',
    `fec_inicio`      DATE            NULL DEFAULT NULL,
    `fec_fin`         DATE            NULL DEFAULT NULL,
    `fec_ultimo_pago` DATE            NULL DEFAULT NULL,
    PRIMARY KEY (`id_suscripcion`),
    UNIQUE KEY `suscripciones_id_usuario_unique` (`id_usuario`),
    CONSTRAINT `suscripciones_id_usuario_foreign`
        FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 10. PAGOS ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `pagos` (
    `id_pago`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_suscripcion`         BIGINT UNSIGNED NOT NULL,
    `paypal_order_id`        VARCHAR(100)    NULL DEFAULT NULL,
    `paypal_transaction_id`  VARCHAR(100)    NULL DEFAULT NULL,
    `mon_monto`              DECIMAL(10,2)   NOT NULL DEFAULT 0,
    `est_pago`               TINYINT         NOT NULL DEFAULT 0 COMMENT '0=pendiente, 1=completado, 2=cancelado, 3=fallido',
    `fec_pago`               DATE            NULL DEFAULT NULL,
    `tipo_pago`              VARCHAR(50)     NOT NULL DEFAULT 'paypal',
    `created_at`             TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`             TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id_pago`),
    KEY `pagos_id_suscripcion_foreign` (`id_suscripcion`),
    CONSTRAINT `pagos_id_suscripcion_foreign`
        FOREIGN KEY (`id_suscripcion`) REFERENCES `suscripciones` (`id_suscripcion`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 11. PERSONAL_ACCESS_TOKENS (Laravel Sanctum) ────────────────────────────
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tokenable_type` VARCHAR(255)    NOT NULL,
    `tokenable_id`   BIGINT UNSIGNED NOT NULL,
    `name`           TEXT            NOT NULL,
    `token`          VARCHAR(64)     NOT NULL,
    `abilities`      TEXT            NULL,
    `last_used_at`   TIMESTAMP       NULL DEFAULT NULL,
    `expires_at`     TIMESTAMP       NULL DEFAULT NULL,
    `created_at`     TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`     TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
    KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`, `tokenable_id`),
    KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 12. SESSIONS (Laravel sesiones web) ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS `sessions` (
    `id`            VARCHAR(255)    NOT NULL,
    `user_id`       BIGINT UNSIGNED NULL DEFAULT NULL,
    `ip_address`    VARCHAR(45)     NULL DEFAULT NULL,
    `user_agent`    TEXT            NULL,
    `payload`       LONGTEXT        NOT NULL,
    `last_activity` INT             NOT NULL,
    PRIMARY KEY (`id`),
    KEY `sessions_user_id_index` (`user_id`),
    KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 13. CACHE ────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `cache` (
    `key`        VARCHAR(255) NOT NULL,
    `value`      MEDIUMTEXT   NOT NULL,
    `expiration` INT          NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache_locks` (
    `key`        VARCHAR(255) NOT NULL,
    `owner`      VARCHAR(255) NOT NULL,
    `expiration` INT          NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 14. JOBS (Laravel Queue) ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `jobs` (
    `id`           BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `queue`        VARCHAR(255)     NOT NULL,
    `payload`      LONGTEXT         NOT NULL,
    `attempts`     TINYINT UNSIGNED NOT NULL,
    `reserved_at`  INT UNSIGNED     NULL DEFAULT NULL,
    `available_at` INT UNSIGNED     NOT NULL,
    `created_at`   INT UNSIGNED     NOT NULL,
    PRIMARY KEY (`id`),
    KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 15. PASSWORD_RESET_TOKENS ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
    `email`      VARCHAR(255) NOT NULL,
    `token`      VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP    NULL DEFAULT NULL,
    PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── 16. MIGRATIONS (tabla interna de Laravel) ────────────────────────────────
CREATE TABLE IF NOT EXISTS `migrations` (
    `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `migration` VARCHAR(255) NOT NULL,
    `batch`     INT          NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ─── FIN DEL SCRIPT ───────────────────────────────────────────────────────────
SELECT 'Base de datos control_asistencias_bd_dev creada correctamente.' AS resultado;
