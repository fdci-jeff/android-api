<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Client\RequestException;
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
    }

    public function getNews(Request $request)
    {
        $url = env('NEWS_API_LINK');

        $pagination = $request->page;
        $limit = 100;

        $offset = $limit * ($pagination - 1);
		$hasnext = $pagination * $limit;

        try{
            $client = new Client(); 
            $result = $client->get($url, [
                'query' => [
                    'countries' => 'ph',
                    'access_key' => env('NEWS_API_KEY'),
                    'limit' => $limit,
                    'sort' =>  'popularity',
                    'offset' => $offset
                ]
            ]);  
            if($result->getStatusCode() == 200){
                $result = json_decode($result->getBody(), true);
                
                $total = $result['pagination']['total'];

                $result['pagination']['has_next'] = $hasnext >= $total ? false : true;

                return response()->json(
                    [
                        'success' => true,
                        'status' => 'News Found',
                        'news'   => $result['data'],
                        'pagination' => $result['pagination']
                    ], 200);

            } else{
                return response()->json(
                    [
                        'status' => 'failed',
                        'message' => 'API request failed'
                    ], 404);
            }

        } catch(RequestException $e){
            return response()->json(
                [
                    'status' => 'failed',
                    'message' => $e->getMessage()
                ], 500);
        }

    }

    public function getLatestNews(Request $request)
    {
        $url = env('NEWS_API_LINK');

        $pagination = $request->page;
        $limit = 100;

        $offset = $limit * ($pagination - 1);
		$hasnext = $pagination * $limit;

        try{
            $client = new Client(); 
            $result = $client->get($url, [
                'query' => [
                    'countries' => 'ph',
                    'access_key' => env('NEWS_API_KEY'),
                    'limit' => $limit,
                    'date' =>  '2023-03-24',
                    'offset' => $offset
                ]
            ]);  
            if($result->getStatusCode() == 200){
                $result = json_decode($result->getBody(), true);
                
                $total = $result['pagination']['total'];

                $result['pagination']['has_next'] = $hasnext >= $total ? false : true;

                return response()->json(
                    [
                        'success' => true,
                        'status' => 'News Found',
                        'news'   => $result['data'],
                        'pagination' => $result['pagination']
                    ], 200);

            } else{
                return response()->json(
                    [
                        'status' => 'failed',
                        'message' => 'API request failed'
                    ], 404);
            }

        } catch(RequestException $e){
            return response()->json(
                [
                    'status' => 'failed',
                    'message' => $e->getMessage()
                ], 500);
        }

    }
}
