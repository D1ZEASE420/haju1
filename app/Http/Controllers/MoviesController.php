<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class MoviesController extends Controller
{
    private const API_URL = 'https://ralfiharjutus.ta24siim.itmajakas.ee/api/movies';
    private const CACHE_TTL = 300; // 5 minutes

    /**
     * Fetch and display movies from external API.
     */
    public function index(Request $request)
    {
        $search = $request->string('search')->trim()->toString();
        $sort   = $request->input('sort', 'title');
        $dir    = $request->input('dir', 'asc');

        // Fetch all movies from external API (cached)
        $movies = Cache::remember('external_movies', self::CACHE_TTL, function () {
            try {
                $response = Http::timeout(10)
                    ->get(self::API_URL);

                if ($response->successful()) {
                    return $response->json();
                }

                return [];
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Movies API fetch failed', [
                    'error' => $e->getMessage()
                ]);
                return null; // null = API unreachable
            }
        });

        // Apply search filter on fetched data
        if ($search && is_array($movies)) {
            $movies = array_values(array_filter($movies, function ($movie) use ($search) {
                $searchLower = strtolower($search);
                return str_contains(strtolower($movie['title'] ?? ''), $searchLower)
                    || str_contains(strtolower($movie['description'] ?? ''), $searchLower)
                    || str_contains(strtolower($movie['director'] ?? ''), $searchLower)
                    || str_contains(strtolower($movie['genre'] ?? ''), $searchLower);
            }));
        }

        // Apply sorting
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