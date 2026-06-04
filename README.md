# ATN — Sistema de Control de Asistencias
**OMEGA Solutions** | Laravel 11 + Flutter | MySQL 8.x

---

## Archivos de instalación

| Archivo | Descripción |
|---------|-------------|
| `README_ATN.docx` | Guía completa de instalación paso a paso |
| `instalar_ATN.ps1` | Script PowerShell — instala todo automáticamente en Windows |
| `create_database_ATN.sql` | Script SQL — crea la base de datos directamente en MySQL |

---

## Instalación rápida (Windows)

**Opción A — Script automático (recomendado)**

```powershell
# Desde la carpeta raíz OMEGA-Backend-Web
.\instalar_ATN.ps1
```

El script verifica requisitos, instala dependencias, configura el `.env` y carga los datos de prueba.

**Opción B — Manual**

```bash
# 1. Crear la base de datos (una sola vez)
mysql -u root -p < create_database_ATN.sql

# 2. Instalar dependencias
cd backend
composer install
npm install

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate
# Editar .env con los datos de MySQL

# 4. Migraciones y datos de prueba
php artisan migrate --seed

# 5. Levantar (dos terminales)
php artisan serve --host=0.0.0.0 --port=8000
npm run dev
```

Abrir en el navegador: `http://localhost:8000`

---

## Credenciales de prueba

Contraseña para todos: **Omega2026**

| Rol | Email |
|-----|-------|
| Docente | cmendoza@omega.com |
| Docente | lgutierrez@omega.com |
| Alumno  | sramirez@omega.com |
| Alumno  | dtorres@omega.com |
| Alumno  | vcastro@omega.com |

Sesión activa lista: **Grupo 216000 — Clave: PRUEBA**

---

## Estructura del repositorio

| Carpeta | Descripción |
|---------|-------------|
| `backend/` | Laravel 11 — API REST + vistas Blade web |
| `mobile/`  | Flutter — app móvil para alumnos y docentes |

