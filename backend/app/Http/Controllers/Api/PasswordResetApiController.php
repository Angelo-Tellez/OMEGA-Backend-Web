<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Api\ForgotPasswordApiRequest;
use App\Http\Requests\Api\ResetPasswordApiRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordResetApiController extends Controller
{
    public function sendResetLink(ForgotPasswordApiRequest $request): JsonResponse
    {
        $usuario = Usuario::where('email', $request->email)->first();

        if ($usuario) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            $token    = Str::random(64);
            DB::table('password_reset_tokens')->insert([
                'email'      => $request->email,
                'token'      => Hash::make($token),
                'created_at' => Carbon::now(),
            ]);

            $resetUrl = route('ca.password.reset', ['token' => $token, 'email' => $request->email]);

            Mail::send('emails.reset-password', [
                'usuario'  => $usuario,
                'resetUrl' => $resetUrl,
            ], function ($m) use ($request) {
                $m->to($request->email)->subject('Restablece tu contraseña — OMEGA');
            });
        }

        return response()->json([
            'message' => 'Si ese correo está registrado, recibirás un enlace para restablecer tu contraseña.',
        ]);
    }

    public function reset(ResetPasswordApiRequest $request): JsonResponse
    {
        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return response()->json(['message' => 'Enlace inválido o expirado.'], 422);
        }

        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['message' => 'El enlace ha expirado. Solicita uno nuevo.'], 422);
        }

        $usuario = Usuario::where('email', $request->email)->first();
        if (!$usuario) return response()->json(['message' => 'Correo no encontrado.'], 422);

        $usuario->update(['contrasenia' => $request->password]);
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Contraseña restablecida correctamente.']);
    }
}
