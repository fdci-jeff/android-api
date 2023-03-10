<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Token;
use GuzzleHttp\Client;
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

    public function getHotel()
    {
        $url = env('AMADEUS_TEST_LINK') ."v1/security/oauth2/token";

        try{
            $client = new Client(); 
            $result = $client->post($url, [
                'form_params' => [
                    'client_id' => env('AMADEUS_CLIENT_ID'),
                    'client_secret' => env('AMADEUS_CLIENT_SECRET'),
                    'grant_type' =>  'client_credentials',
                ]
            ]);  
            if($result->getStatusCode()){
                $result = json_decode($result->getBody());
                

                $params = [
                    'hotelIds' => 'MCLONGHM'
                ];

                $hotel_url = env('AMADEUS_TEST_LINK')."v3/shopping/hotel-offers"."?".http_build_query($params);
                $authorization = "Bearer ".$result->access_token;
                $requestParams=[
                    'headers' => ['Content-Type' => 'application/vnd.amadeus+json','Authorization' => $authorization],
                    'verify' => false,
                ];

                $hotel = $client->get($hotel_url, $requestParams);
                if($hotel->getStatusCode()){
                    $content = json_decode($hotel->getBody());
                
                    return $content;
    
                }

            } else{
                return false;
            }

        } catch(GuzzleException $exception){
            // $response = $exception->getResponse();
            // $result= json_decode($response->getBody()->getContents());

            return false;
        }

    }
}
