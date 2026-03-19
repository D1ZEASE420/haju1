<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class WeatherController extends Controller
{
    public function index(Request $request)
    {
        $city = $request->input('city', 'Tallinn');

        $weather = Cache::remember("weather_$city", 600, function () use ($city) {
            return Http::get('https://api.openweathermap.org/data/2.5/weather', [
                'q' => $city,
                'appid' => env('WEATHER_API_KEY'),
                'units' => 'metric'
            ])->json();
        });

        return inertia('Weather', [
            'weather' => $weather
        ]);
    }

    public function search(Request $request)
    {
        $city = $request->input('city', 'Tallinn');

        $weather = Cache::remember("weather_$city", 600, function () use ($city) {
            return Http::get('https://api.openweathermap.org/data/2.5/weather', [
                'q' => $city,
                'appid' => env('WEATHER_API_KEY'),
                'units' => 'metric'
            ])->json();
        });

        return inertia('Weather', [
            'weather' => $weather
        ]);
    }
}