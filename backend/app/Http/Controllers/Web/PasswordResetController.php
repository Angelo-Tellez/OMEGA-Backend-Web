<?php

/*
 * ============================================================
 * Controlador Web — Recuperación y Restablecimiento de Contraseña.
 * MPL-OMEGA-05 §6.1 | §6.6
 * @version 1.0.0
 * ============================================================
 */

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
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

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email'    => 'Ingresa un correo válido',
        ]);

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

    public function reset(Request $request)
    {
        $request->validate([
            'token'                 => ['required'],
            'email'                 => ['required', 'email'],
            'password'              => ['required', 'min:8', 'confirmed'],
        ], [
            'password.required'  => 'La contraseña es obligatoria',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
        ]);

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
