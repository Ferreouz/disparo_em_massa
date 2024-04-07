<?php

namespace App\Http\Controllers;

use App\Models\Number;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class QrcodeAPIController extends Controller
{
    public $number;
    public function index(string $instance)
    {
        $number = $this->number ?? Number::withoutGlobalScopes()->where('instance', $instance)->first();
        if(!$number)
        {
            return ['error' => 'instance not found'];
        }

        $response = Http::withHeaders([
            'apikey' => $number->token,
            'Accept'=> 'application/json'
        ])
        ->get(env('EVOLUTION_URL'). "/instance/connect/$number->instance");
        
        if(!$response->ok()) 
        {
            return ['error' => 'instance not found'];
        }

        return ['base64' => $response->json()['base64'] ];
    }
    public function reload(string $instance)
    {
        $number = Number::withoutGlobalScopes()->where('instance', $instance)->first();
        if(!$number)
        {
            return ['error' => 'instance not found'];
        }

        $response = Http::withHeaders([
            'apikey' => $number->token,
            'Accept'=> 'application/json'
        ])
        ->delete(env('EVOLUTION_URL'). "/instance/logout/$number->instance");
        
        $data = $this->index($instance);
        return $data;
    }
}
