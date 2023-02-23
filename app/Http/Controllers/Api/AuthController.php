<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\RegisterRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\TokenRequest;
use Illuminate\Http\Request;
use App\Models\Token;
use App\Models\User;
use JwtApi;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
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
    public function logout() {
        $refresh_obj = Token::findPairByValue( auth()->getToken()->get() );
        auth()->logout();
        auth()->setToken($refresh_obj->value)->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh() {
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
    public function register(RegisterRequest $request) {
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

    protected function respondWithToken($access_token) {
        $response_array = [
            'access_token' => $access_token,
            'token_type' => 'bearer',
            'access_expires_in' => auth('api')->factory()->getTTL() * 60,
        ];
        
        $access_obj = Token::create([
            'user_id' => auth()->user()->id,
            'value' => $access_token,
            'jti' => auth()->payload()->get('jti'),
            'type' => auth()->payload()->get('xtype'),
            'payload' => auth()->payload()->toArray(),
            'ip' => JwtApi::getIp(),
            'device' => JwtApi::getUserAgent()
        ]);
        
        $refresh_token = auth()->claims([
            'xtype' => 'refresh',
            'xpair' => auth()->payload()->get('jti')
        ])->setTTL(auth('api')->factory()->getTTL() * 3)->tokenById(auth()->user()->id);
        
        $response_array +=[
            'refresh_token' => $refresh_token,
            'refresh_expires_in' => auth('api')->factory()->getTTL() * 60
        ];
        
        $refresh_obj = Token::create([
            'user_id' => auth()->user()->id,
            'value' => $refresh_token,
            'jti' => auth()->setToken($refresh_token)->payload()->get('jti'),
            'type' => auth()->setToken($refresh_token)->payload()->get('xtype'),
            'pair' => $access_obj->id,
            'payload' => auth()->setToken($refresh_token)->payload()->toArray(),
            'ip' => JwtApi::getIp(),
            'device' => JwtApi::getUserAgent()
        ]);
        
        $access_obj->pair = $refresh_obj->id;
        $access_obj->save();

        return response()->json($response_array);
    }

    public function profile() {
        return response()->json(auth()->user());
    }

    public function logOutAll(Request $request) {
        foreach( auth()->user()->token as $token_obj ){
            try{
              auth()->setToken( $token_obj->value )->invalidate(true);
            } catch (Exception $e){
              //do nothing, it's already bad token for various reasons
            }
          }
          return response()->json(['message' => 'Successfully logged out from all devices']);
    }
    
    public function tokenIssue(TokenRequest $request) {
        $resource_token = auth()->claims([
            'xtype' => 'resource'
        ])->setTTL(60 * 24 * 365)->tokenById(auth()->user()->id);

        $resource_token_obj = Token::create([
            'user_id' => auth()->user()->id,
            'value' => $resource_token,
            'jti' => auth()->setToken($resource_token)->payload()->get('jti'),
            'type' => auth()->setToken($resource_token)->payload()->get('xtype'),
            'pair' => null,
            'payload' => auth()->setToken($resource_token)->payload()->toArray(),
            'grants' => [
              'id' => $request->input('id'),
              'name' => $request->input('name'),
              'email' => $request->input('email')
            ],
            'ip' => null,
            'device' => null
        ]);
        
        return response()->json(['token' => $resource_token]);
    }
}
