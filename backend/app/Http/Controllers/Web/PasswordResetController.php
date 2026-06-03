<?php

// ============================================================
// Company    : OMEGA Solutions (OMEGA)
// Project    : ATN - Sistema de Control de Asistencias
// File       : app/Http/Controllers/Web/PasswordResetController.php
// Created on : 03/06/2026
// Created by : Angelo Armando Tellez Enriquez
// Reviewed by:
// ------------------------------------------------------------
// Changelog:
//   [001] 03/06/2026 - Angelo Armando Tellez Enriquez - Creacion del archivo
// ============================================================


namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Http\Requests\Web\ForgotPasswordRequest;
use App\Http\Requests\Web\ResetPasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * RF-55 — Recuperación de contraseña por correo electrónico.
 */
class PasswordResetController extends Controller
{
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(ForgotPasswordRequest $request)
    {
        $usuario = Usuario::where('email', $request->email)->first();

        // Por seguridad siempre mostramos el mismo mensaje
        if ($usuario) {
            // Eliminar tokens anteriores
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            $token = Str::random(64);

            DB::table('password_reset_tokens')->insert([
                'email'      => $request->email,
                'token'      => Hash::make($token),
                'created_at' => Carbon::now(),
            ]);

            $resetUrl = route('ca.password.reset', [
                'token' => $token,
                'email' => $request->email,
            ]);

            Mail::send('emails.reset-password', [
                'usuario'  => $usuario,
                'resetUrl' => $resetUrl,
            ], function ($message) use ($request) {
                $message->to($request->email)
                        ->subject('Restablece tu contraseña — OMEGA');
            });
        }

        return back()->with('status',
            'Si ese correo está registrado, recibirás un enlace para restablecer tu contraseña'
        );
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function reset(ResetPasswordRequest $request)
    {
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'El enlace de recuperación no es válido o ha expirado']);
        }

        // Verificar que no haya expirado (60 minutos)
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'El enlace de recuperación ha expirado, solicita uno nuevo']);
        }

        $usuario = Usuario::where('email', $request->email)->first();

        if (!$usuario) {
            return back()->withErrors(['email' => 'No encontramos una cuenta con ese correo']);
        }

        $usuario->update(['contrasenia' => $request->password]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('ca.login')
            ->with('success', 'Contraseña restablecida correctamente, ya puedes iniciar sesión');
    }
}
