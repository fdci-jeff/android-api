<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\RegisterRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('jwt.verify', ['except' => ['login', 'register']]);
        $this->middleware('jwt.xauth', ['except' => ['login', 'register', 'refresh']]);
        $this->middleware('jwt.xrefresh', ['only' => ['refresh']]);
    }

     /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request) {
        
        $credentials = $request->getCredentials();

        $token = auth()->attempt($credentials);

        if (!$access_token = auth()->claims(['xtype' => 'auth'])->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($access_token);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        
        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $access_token = auth()->claims(['xtype' => 'auth'])->refresh(true,true);
		auth()->setToken($access_token); 

        return $this->respondWithToken($access_token);
    }

    /**
     * Register new user
     *
     * @param  string $name, $email, $password, password_confirmation
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        $user = User::create($request->validated());

        return response()->json([
            'message' => 'User created.',
                'user' => $user
            ]);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'refresh_token' => auth()->claims([
                'xtype' => 'refresh',
                'xpair' => auth()->payload()->get('jti')
            ])
            ->setTTL(auth('api')->factory()->getTTL() * 3)
            ->tokenById(auth()->user()->id),
            'refresh_expires_in' => auth('api')->factory()->getTTL() * 60,
        ]);
    }

    public function profile()
    {
        return response()->json(auth()->user());
    }
}
