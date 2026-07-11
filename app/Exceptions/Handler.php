<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Session expirée (CSRF token invalide) : plutôt que d'afficher la
        // page d'erreur 419 brute, on redirige vers la connexion avec un
        // message clair pour les soumissions de formulaire classiques, et
        // une réponse JSON propre pour les appels AJAX/fetch (interceptés
        // côté client par le script de layouts/dashboard.blade.php).
        //
        // On cible HttpException (code 419) plutôt que TokenMismatchException
        // directement : Handler::prepareException() convertit déjà cette
        // dernière en HttpException(419, ...) avant que les callbacks
        // renderable() ne soient évalués, donc un type-hint sur
        // TokenMismatchException ne matcherait jamais.
        $this->renderable(function (HttpException $e, $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Votre session a expiré. Veuillez vous reconnecter.',
                ], 419);
            }

            return redirect()->route('login')
                ->with('warning', 'Votre session a expiré. Veuillez vous reconnecter.');
        });
    }
}
