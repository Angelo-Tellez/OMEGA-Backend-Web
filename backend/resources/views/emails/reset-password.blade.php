{{--
// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : resources/views/emails/reset-password.blade.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablece tu contraseña</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
        .container { max-width: 560px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; }
        .header { background: #2C3E6B; padding: 32px; text-align: center; }
        .header h1 { color: white; margin: 0; font-size: 22px; }
        .header p { color: #a0b0d0; margin: 8px 0 0; font-size: 13px; }
        .body { padding: 32px; }
        .body p { color: #444; font-size: 15px; line-height: 1.6; }
        .btn { display: block; width: fit-content; margin: 24px auto; padding: 14px 32px; background: #F28B66; color: white; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 15px; }
        .footer { padding: 20px 32px; border-top: 1px solid #eee; }
        .footer p { color: #999; font-size: 12px; margin: 0; }
        .note { background: #f9f9f9; border-left: 3px solid #F28B66; padding: 12px 16px; border-radius: 4px; margin-top: 20px; }
        .note p { color: #666; font-size: 13px; margin: 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>OMEGA</h1>
            <p>Sistema de Control de Asistencias</p>
        </div>
        <div class="body">
            <p>Hola, <strong>{{ $usuario->nombre }} {{ $usuario->ap_pat }}</strong></p>
            <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta. Haz clic en el botón para continuar:</p>

            <a href="{{ $resetUrl }}" class="btn">Restablecer contraseña</a>

            <div class="note">
                <p>⏱ Este enlace expira en <strong>60 minutos</strong>.</p>
                <p style="margin-top:8px">Si no solicitaste este cambio, ignora este correo — tu contraseña no cambiará.</p>
            </div>
        </div>
        <div class="footer">
            <p>© 2026 OMEGA Solutions · Si el botón no funciona, copia y pega este enlace: {{ $resetUrl }}</p>
        </div>
    </div>
</body>
</html>
