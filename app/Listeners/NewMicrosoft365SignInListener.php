<?php

namespace App\Listeners;

use App\Models\User;
use Dcblogdev\MsGraph\MsGraph;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class NewMicrosoft365SignInListener
{
    public function handle(object $event): void
    {
        // Obtener el email del usuario desde Azure
        $email = $event->token['info']['mail'] ?? $event->token['info']['userPrincipalName'];

        // dd($email);

        // Verificar si el usuario existe en la base de datos
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Si el usuario no existe, redirigir con error
            abort(Response::HTTP_FORBIDDEN, 'No tienes permiso para acceder con este correo.');
        }

        // Guardar el token en MsGraph para futuras solicitudes
        (new MsGraph())->storeToken(
            $event->token['accessToken'],
            $event->token['refreshToken'],
            $event->token['expires'],
            $user->id,
            $user->email
        );

        // Autenticar al usuario en Laravel
        Auth::login($user);

        // Redirigir al dashboard
        redirect()->route('dashboard');
    }
}
