<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Token;
use JwtApi;

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
        $token_obj = Token::findByValue(auth()->getToken()->get());
        $grantedAttr=[];
        foreach ( $token_obj->grants as $grant=>$val ){
          if ( $val ) array_push($grantedAttr, $grant);
        }
        return response()->json(['user' => auth()->user()->only($grantedAttr) ], 200);
    }
}
