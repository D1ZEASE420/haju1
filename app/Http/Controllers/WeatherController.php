<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class WeatherController extends Controller
{
    private function fetchWeather(string $city): array|null
    {
        $apiKey = env('OPENWEATHER_API_KEY');

        if (empty($apiKey)) {
            return null;
        }

        try {
            $response = Http::timeout(5)->get('https://api.openweathermap.org/data/2.5/weather', [
                'q'     => $city,
                'appid' => $apiKey,
                'units' => 'metric',
            ]);

            $data = $response->json();

            // Return null if API returned an error
            if (!isset($data['cod']) || $data['cod'] != 200) {
                return $data; // pass error through so frontend can show it
            }

            return $data;
        } catch (\Exception $e) {
            return ['cod' => 500, 'message' => 'Weather service unavailable. Please try again later.'];
        }
    }

    public function index(Request $request)
    {
        $city = $request->input('city', 'Tallinn');

        $weather = Cache::remember("weather_{$city}", 600, fn () => $this->fetchWeather($city));

        return inertia('Weather', ['weather' => $weather]);
    }

    public function search(Request $request)
    {
        $city = $request->input('city', 'Tallinn');

        // Don't cache error results
        $weather = $this->fetchWeather($city);
        if ($weather && isset($weather['cod']) && $weather['cod'] == 200) {
            Cache::put("weather_{$city}", $weather, 600);
        }

        return inertia('Weather', ['weather' => $weather]);
    }
}
