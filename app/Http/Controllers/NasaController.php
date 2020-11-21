<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NasaController extends Controller
{
    public function EPIC(Request $request )
    {
       $url = "https://api.nasa.gov/EPIC/api/natural/1999-08-14?api_key=sU4B1emHokHQxqsRBYVjuLDTV3lIvQgfer6xrhHy";
       $response = Http::timeout(3)->get($url)->json();
       return $response;
    }

    public function EART(Request $request)
    {
        $url = "https://api.nasa.gov/planetary/earth/assets?lon=-95.33&lat=29.78&date=2018-09-21&&dim=0.10&api_key=sU4B1emHokHQxqsRBYVjuLDTV3lIvQgfer6xrhHy";
        $response = Http::get($url)->json();
        return $response;
    }
    public function InSight(Request $request)
    {
        $url = "https://api.nasa.gov/insight_weather/?api_key=sU4B1emHokHQxqsRBYVjuLDTV3lIvQgfer6xrhHy&feedtype=json&ver=1.0";
        $response = Http::get($url)->json();
        return $response;
    }
}
