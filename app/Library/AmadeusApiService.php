<?php

namespace App\Library;

use GuzzleHttp\Client;

class AmadeusApiService extends Client
{
    private $apikey = AMADEUS_API_KEY;

    public function __construct($apikey)
    {
        $this->apiKey = $apikey;

        parent::__construct([
            'base_uri' => 'test.api.amadeus.com/v3'
        ]);
    }
}

