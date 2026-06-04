# ==============================================================
# instalar_ATN.ps1
# Script de instalacion automatica — ATN Sistema de Control de Asistencias
# OMEGA Solutions
#
# USO:
#   1. Abre PowerShell como Administrador
#   2. Navega a la carpeta raiz del proyecto (OMEGA-Backend-Web)
#   3. Ejecuta: .\instalar_ATN.ps1
#
# REQUISITOS PREVIOS:
#   - PHP 8.2+ instalado y en el PATH
#   - Composer instalado y en el PATH
#   - Node.js 18+ instalado y en el PATH
#   - MySQL 8.x corriendo en localhost:3306
#   - Git instalado
# ==============================================================

Write-Host ""
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "   ATN — Sistema de Control de Asistencias" -ForegroundColor Cyan
Write-Host "   Script de Instalacion y Carga de Datos de Prueba" -ForegroundColor Cyan
Write-Host "   OMEGA Solutions 2026" -ForegroundColor Cyan
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host ""

# ── PASO 0: Verificar que estamos en el directorio correcto ────────────────
if (-not (Test-Path "backend\artisan")) {
    if (Test-Path "artisan") {
        # Ya estamos dentro de /backend
        Set-Location ..
    } else {
        Write-Host "[ERROR] No se encontro el proyecto." -ForegroundColor Red
        Write-Host "Ejecuta este script desde la carpeta raiz OMEGA-Backend-Web" -ForegroundColor Yellow
        exit 1
    }
}

Set-Location backend
Write-Host "[INFO] Directorio de trabajo: $(Get-Location)" -ForegroundColor Gray
Write-Host ""

# ── PASO 1: Verificar PHP ──────────────────────────────────────────────────
Write-Host "[1/7] Verificando PHP..." -ForegroundColor Yellow
try {
    $phpVer = php -r "echo PHP_VERSION;" 2>&1
    Write-Host "      PHP encontrado: $phpVer" -ForegroundColor Green
} catch {
    Write-Host "[ERROR] PHP no encontrado. Instalalo y agregaloo al PATH." -ForegroundColor Red
    exit 1
}

# ── PASO 2: Verificar Composer ─────────────────────────────────────────────
Write-Host "[2/7] Verificando Composer..." -ForegroundColor Yellow
try {
    $composerVer = composer --version 2>&1 | Select-Object -First 1
    Write-Host "      $composerVer" -ForegroundColor Green
} catch {
    Write-Host "[ERROR] Composer no encontrado. Instalalo desde https://getcomposer.org" -ForegroundColor Red
    exit 1
}

# ── PASO 3: Verificar Node.js ──────────────────────────────────────────────
Write-Host "[3/7] Verificando Node.js..." -ForegroundColor Yellow
try {
    $nodeVer = node --version 2>&1
    Write-Host "      Node.js encontrado: $nodeVer" -ForegroundColor Green
} catch {
    Write-Host "[ERROR] Node.js no encontrado. Instalalo desde https://nodejs.org" -ForegroundColor Red
    exit 1
}

# ── PASO 4: Instalar dependencias PHP ─────────────────────────────────────
Write-Host ""
Write-Host "[4/7] Instalando dependencias PHP con Composer..." -ForegroundColor Yellow
composer install --no-interaction --prefer-dist
if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] Fallo composer install." -ForegroundColor Red
    exit 1
}
Write-Host "      Dependencias PHP instaladas correctamente." -ForegroundColor Green

# ── PASO 5: Instalar dependencias Node.js ─────────────────────────────────
Write-Host ""
Write-Host "[5/7] Instalando dependencias Node.js con npm..." -ForegroundColor Yellow
npm install
if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] Fallo npm install." -ForegroundColor Red
    exit 1
}
Write-Host "      Dependencias Node.js instaladas correctamente." -ForegroundColor Green

# ── PASO 6: Configurar .env ────────────────────────────────────────────────
Write-Host ""
Write-Host "[6/7] Configurando el entorno (.env)..." -ForegroundColor Yellow

if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host "      Archivo .env creado desde .env.example." -ForegroundColor Green
} else {
    Write-Host "      El archivo .env ya existe, se conserva." -ForegroundColor Gray
}

# Configurar conexion MySQL
Write-Host ""
Write-Host "      Configuracion de la base de datos MySQL:" -ForegroundColor Cyan
$dbHost     = Read-Host "      Host MySQL (Enter para 127.0.0.1)"
$dbPort     = Read-Host "      Puerto MySQL (Enter para 3306)"
$dbName     = Read-Host "      Nombre de la BD (Enter para control_asistencias_bd_dev)"
$dbUser     = Read-Host "      Usuario MySQL (Enter para root)"
$dbPassRaw  = Read-Host "      Contrasena MySQL (Enter para dejar vacia)"

if ([string]::IsNullOrWhiteSpace($dbHost))  { $dbHost = "127.0.0.1" }
if ([string]::IsNullOrWhiteSpace($dbPort))  { $dbPort = "3306" }
if ([string]::IsNullOrWhiteSpace($dbName))  { $dbName = "control_asistencias_bd_dev" }
if ([string]::IsNullOrWhiteSpace($dbUser))  { $dbUser = "root" }

# Actualizar valores en .env
$envContent = Get-Content ".env" -Raw
$envContent = $envContent -replace "DB_CONNECTION=.*",  "DB_CONNECTION=mysql"
$envContent = $envContent -replace "#?\s*DB_HOST=.*",   "DB_HOST=$dbHost"
$envContent = $envContent -replace "#?\s*DB_PORT=.*",   "DB_PORT=$dbPort"
$envContent = $envContent -replace "#?\s*DB_DATABASE=.*","DB_DATABASE=$dbName"
$envContent = $envContent -replace "#?\s*DB_USERNAME=.*","DB_USERNAME=$dbUser"
$envContent = $envContent -replace "#?\s*DB_PASSWORD=.*","DB_PASSWORD=$dbPassRaw"
$envContent = $envContent -replace "APP_URL=.*",        "APP_URL=http://localhost:8000"
Set-Content ".env" $envContent
Write-Host "      .env actualizado con los datos de conexion." -ForegroundColor Green

# Generar APP_KEY
php artisan key:generate --ansi
if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] Fallo key:generate." -ForegroundColor Red
    exit 1
}

# ── PASO 7: Migraciones y Seeder ───────────────────────────────────────────
Write-Host ""
Write-Host "[7/7] Ejecutando migraciones y cargando datos de prueba..." -ForegroundColor Yellow
Write-Host "      Esto creara todas las tablas e insertara los datos de evaluacion." -ForegroundColor Gray
Write-Host ""

php artisan migrate:fresh --seed --force
if ($LASTEXITCODE -ne 0) {
    Write-Host "[ERROR] Fallo migrate:fresh --seed." -ForegroundColor Red
    Write-Host "Verifica que MySQL este corriendo y que la base de datos '$dbName' exista." -ForegroundColor Yellow
    Write-Host "Puedes crearla con: CREATE DATABASE $dbName CHARACTER SET utf8mb4;" -ForegroundColor Yellow
    exit 1
}

# ── RESULTADO FINAL ────────────────────────────────────────────────────────
Write-Host ""
Write-Host "============================================================" -ForegroundColor Green
Write-Host "   INSTALACION COMPLETADA EXITOSAMENTE" -ForegroundColor Green
Write-Host "============================================================" -ForegroundColor Green
Write-Host ""
Write-Host "Para iniciar el sistema necesitas DOS terminales:" -ForegroundColor Cyan
Write-Host ""
Write-Host "  Terminal 1 — Servidor Laravel:" -ForegroundColor White
Write-Host "    php artisan serve --host=0.0.0.0 --port=8000" -ForegroundColor Yellow
Write-Host ""
Write-Host "  Terminal 2 — Compilador de assets:" -ForegroundColor White
Write-Host "    npm run dev" -ForegroundColor Yellow
Write-Host ""
Write-Host "Luego abre en el navegador:" -ForegroundColor White
Write-Host "    http://localhost:8000" -ForegroundColor Cyan
Write-Host ""
Write-Host "------------------------------------------------------------" -ForegroundColor Gray
Write-Host "CREDENCIALES DE PRUEBA (contrasena: Omega2026)" -ForegroundColor White
Write-Host ""
Write-Host "  DOCENTES:" -ForegroundColor Cyan
Write-Host "    cmendoza@omega.com     -> TecToluca (216000, 216001, 216002)" -ForegroundColor Gray
Write-Host "    lgutierrez@omega.com   -> UAEM (301A, 302B, 303C)" -ForegroundColor Gray
Write-Host ""
Write-Host "  ALUMNOS TecToluca:" -ForegroundColor Cyan
Write-Host "    sramirez@omega.com  dtorres@omega.com  vcastro@omega.com" -ForegroundColor Gray
Write-Host "    aflores@omega.com   cherrera@omega.com" -ForegroundColor Gray
Write-Host ""
Write-Host "  ALUMNOS UAEM:" -ForegroundColor Cyan
Write-Host "    lvargas@omega.com   imorales@omega.com  sruiz@omega.com" -ForegroundColor Gray
Write-Host "    fsalinas@omega.com  mromero@omega.com" -ForegroundColor Gray
Write-Host ""
Write-Host "  SESION ACTIVA LISTA:" -ForegroundColor Cyan
Write-Host "    Grupo 216000 (Auditoria) — Clave: PRUEBA" -ForegroundColor Yellow
Write-Host "------------------------------------------------------------" -ForegroundColor Gray
Write-Host ""
