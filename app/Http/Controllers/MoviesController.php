<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class MoviesController extends Controller
{
    private const API_URL = 'https://ralfiharjutus.ta24siim.itmajakas.ee/api/movies';
    private const CACHE_TTL = 300;

    public function index(Request $request)
    {
        $search = $request->string('search')->trim()->toString();
        $sort   = $request->input('sort', 'title');
        $dir    = $request->input('dir', 'asc');

        $movies = Cache::remember('external_movies', self::CACHE_TTL, function () {
            try {
                $response = Http::timeout(10)->get(self::API_URL);

                if ($response->successful()) {
                    $data = $response->json();

                    // Kui API tagastab { data: [...] } või { movies: [...] } jms wrapper
                    if (is_array($data) && !array_is_list($data)) {
                        foreach (['data', 'movies', 'results', 'items', 'films'] as $key) {
                            if (isset($data[$key]) && is_array($data[$key])) {
                                return array_values($data[$key]);
                            }
                        }
                        // Kui wrapper sisaldab ainult skalaarset väärtust, tagasta tühi
                        return [];
                    }

                    // Puhtas massiiv – veendu et iga element on objekt/massiiv, mitte skalaar
                    if (is_array($data)) {
                        $filtered = array_values(array_filter($data, fn($item) => is_array($item)));
                        return $filtered;
                    }

                    return [];
                }

                return [];
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Movies API fetch failed', [
                    'error' => $e->getMessage()
                ]);
                return null;
            }
        });

        if ($search && is_array($movies)) {
            $movies = array_values(array_filter($movies, function ($movie) use ($search) {
                $s = strtolower($search);
                return str_contains(strtolower($movie['title'] ?? ''), $s)
                    || str_contains(strtolower($movie['description'] ?? ''), $s)
                    || str_contains(strtolower($movie['director'] ?? ''), $s)
                    || str_contains(strtolower($movie['genre'] ?? ''), $s);
            }));
        }

        if (is_array($movies) && count($movies) > 0) {
            usort($movies, function ($a, $b) use ($sort, $dir) {
                $aVal = $a[$sort] ?? '';
                $bVal = $b[$sort] ?? '';
                $cmp  = is_numeric($aVal) && is_numeric($bVal)
                    ? $aVal <=> $bVal
                    : strcasecmp((string) $aVal, (string) $bVal);
                return $dir === 'desc' ? -$cmp : $cmp;
            });
        }

        return Inertia::render('Movies/Index', [
            'movies'  => $movies ?? [],
            'error'   => $movies === null ? 'Filmide API ei ole hetkel kättesaadav.' : null,
            'filters' => compact('search', 'sort', 'dir'),
        ]);
    }
}