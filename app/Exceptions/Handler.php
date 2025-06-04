<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $exception)
    {
        // Gestion spécifique des erreurs JWT
        if ($exception instanceof TokenExpiredException) {
            return response()->json(['status' => 'error', 'message' => 'Le token a expiré'], 401);
        }

        if ($exception instanceof TokenInvalidException) {
            return response()->json(['status' => 'error', 'message' => 'Le token est invalide'], 401);
        }

        if ($exception instanceof JWTException) {
            return response()->json(['status' => 'error', 'message' => 'Le token est absent'], 401);
        }

        // Gestion générique des erreurs d’authentification
        if ($exception instanceof AuthenticationException) {
            return response()->json(['status' => 'error', 'message' => 'Non authentifié'], 401);
        }

        return parent::render($request, $exception);
    }
}
