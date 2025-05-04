<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Traits\ApiResponse;

class JWTMiddleware
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Verifica se o token é válido
            JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException $e) {
            return $this->errorResponse('Token expirado', 401);
        } catch (TokenInvalidException $e) {
            return $this->errorResponse('Token inválido', 401);
        } catch (JWTException $e) {
            return $this->errorResponse('Token não encontrado', 401);
        }

        return $next($request);
    }
} 