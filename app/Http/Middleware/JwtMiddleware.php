<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class JwtMiddleware extends BaseMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json([
                    'code'   => 103,
                    'status' => false,
                    'message' => 'Token is Invalid'
                ]);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){     
                // If the token is expired, then it will be refreshed and added to the headers
                try
                {
                  $refreshed = JWTAuth::refresh(JWTAuth::getToken());
                  $user = JWTAuth::setToken($refreshed)->toUser();
                  $request->headers->set('Authorization','Bearer '.$refreshed);
                }catch (JWTException $e){
                    return response()->json([
                        'code'   => 103,
                        'status' => false,
                        'message' => 'Token cannot be refreshed, please Login again'
                    ]);
                }           
                // return response()->json([
                //     'code'   => 103,
                //     'status' => false,
                //     'message' => 'Token cannot be refreshed, please Login again'
                // ]);
            }else{
                return response()->json([
                    'code' => 401,
                    'status' => false,
                    'message' => 'Unauthorized'
                ]);
            }
        }
        return $next($request);
    }
}