<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use App\Models\Token;
use Exception;
use JWTAuth;

class JwtMiddleware extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
		        return response()->json(['status' => 'Token is Invalid'], 403);
		    } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
			    return response()->json(['status' => 'Token is Expired'], 401);
		    } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenBlacklistedException) {
			    return response()->json(['status' => 'Token is Blacklisted'], 400);
            }else{
                return response()->json(['status' => 'Authorization Token not found'], 404);
            }
        }
        
        $token = Token::findByValue( auth()->getToken()->get() );
        
        if (!$token) {
            return response()->json(['status' => 'Token Invalid - bad issuer'], 403);
        }
        
        return $next($request);
    }
}
