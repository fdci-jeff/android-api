<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    protected $table = 'tokens';

    protected $fillable = [
        'user_id',
        'value',
        'jti',
        'type',
        'pair',
        'status',
        'payload'
    ];

    protected $casts = [
        'payload' => 'array'
    ];
  
    public function user() {
        return $this->belongsTo( 'App\Models\User' );
    }

    public static function findByValue($value) {
        $token_obj = self::where('value', $value)->first();
        
        if ($token_obj) {
            return $token_obj;
        }
        
        return null;
    }
    
    public static function findPairByValue($token) {
        $token_obj = self::findByValue($token);
        return self::find($token_obj->pair);
    }
}
