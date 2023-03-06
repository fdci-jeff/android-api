<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Token;
use JwtApi;
use JWTAuth;

class UserController extends Controller
{
    /**
     * Create a new ResController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('jwt.xresource');
    }
    
    public function user (Request $request) {

        try {
			$payload = JWTAuth::getPayload($request->token);
            $user_id = $payload->get('sub');;
 		} catch (Exception $e) {
        	if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
				return response()->json(['status' => 'Token is Invalid'], 403);
			}else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
				return response()->json(['status' => 'Token is Expired'], 401);
			}else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenBlacklistedException){
				return response()->json(['status' => 'Token is Blacklisted'], 400);
			}else{
				return response()->json(['status' => 'Authorization Token not found'], 404);
			}
		}

        // Fetch the user record from the database
        $user = User::find($user_id);

        // Check if the user exists
        if (!$user) {
            return response()->json(['status' => 'User not found', 'success' => false], 404);
        }

        return response()->json(['status' => 'User found', 'success' => true, 'user' => $user], 200);
        // $token_obj = Token::findByValue(auth()->getToken()->get());
        // $grantedAttr=[];
        // foreach ( $token_obj->grants as $grant=>$val ){
        //   if ( $val ) array_push($grantedAttr, $grant);
        // }
        // return response()->json(['user' => auth()->user()->only($grantedAttr) ], 200);
    }
}
