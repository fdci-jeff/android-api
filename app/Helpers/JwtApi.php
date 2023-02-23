<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use \Exception;

class JwtApi 
{
  public static function getIp() {
    foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
      if (array_key_exists($key, $_SERVER) === true){
        foreach (explode(',', $_SERVER[$key]) as $ip){
          $ip = trim($ip);
          if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
            return $ip;
          }
        }
      }
    }
    return \Request::ip();
  }

  public static function getUserAgent() {
    return $_SERVER['HTTP_USER_AGENT'];
  }
}