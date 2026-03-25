<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Music Albums JSON API
 *
 * Base URL: /api/albums
 *
 * GET /api/albums
 *   List albums with optional filtering, sorting, searching and pagination.
 *
 *   Query parameters:
 *     search        string   Search in title and artist fields
 *     genre         string   Filter by exact genre
 *     rating        int      Filter by exact rating (1-5)
 *     year          int      Filter by release year
 *     sort          string   Field to sort by: title|artist|release_year|rating|created_at (default: created_at)
 *     dir           string   Sort direction: asc|desc (default: desc)
 *     limit         int      Max number of results to return (default: 20, max: 100)
 *
 *   Example:
 *     /api/albums?search=pink&genre=Rock&sort=release_year&dir=asc&limit=5
 *
 * GET /api/albums/{id}
 *   Get a single album by ID.
 *
 * GET /api/albums/meta/genres
 *   List all unique genres.
 *
 * GET /api/albums/meta/stats
 *   Summary statistics (count, avg rating, year range).
 */
class AlbumApiController extends Controller
{
    /**
     * GET /api/albums
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->string('search')->trim()->toString();
        $genre  = $request->string('genre')->trim()->toString();
        $rating = $request->integer('rating');
        $year   = $request->integer('year');
        $sort   = in_array($request->sort, ['title', 'artist', 'release_year', 'rating', 'created_at'])
                    ? $request->sort : 'created_at';
        $dir    = $request->dir === 'asc' ? 'asc' : 'desc';
        $limit  = min((int) ($request->limit ?: 20), 100);

        $cacheKey = 'api_albums_' . md5(serialize(compact('search', 'genre', 'rating', 'year', 'sort', 'dir', 'limit')));

        $data = Cache::remember($cacheKey, 120, function () use ($search, $genre, $rating, $year, $sort, $dir, $limit) {
            return Album::with('user:id,name')
                ->when($search, fn ($q) =>
                    $q->where(fn ($q2) =>
                        $q2->where('title', 'like', "%{$search}%")
                           ->orWhere('artist', 'like', "%{$search}%")
                    )
                )
                ->when($genre,  fn ($q) => $q->where('genre', $genre))
                ->when($rating, fn ($q) => $q->where('rating', $rating))
                ->when($year,   fn ($q) => $q->where('release_year', $year))
                ->orderBy($sort, $dir)
                ->limit($limit)
                ->get()
                ->toArray(); // convert to plain array before caching
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'count'   => count($data),
                'limit'   => $limit,
                'filters' => compact('search', 'genre', 'rating', 'year', 'sort', 'dir'),
            ],
        ]);
    }

    /**
     * GET /api/albums/{id}
     */
    public function show(int $id): JsonResponse
    {
        $album = Cache::remember("api_album_{$id}", 300, fn () =>
            Album::with('user:id,name')->findOrFail($id)->toArray()
        );

        return response()->json(['data' => $album]);
    }

    /**
     * GET /api/albums/meta/genres
     */
    public function genres(): JsonResponse
    {
        $genres = Cache::remember('api_albums_genres', 300, fn () =>
            Album::distinct()->orderBy('genre')->pluck('genre')->toArray()
        );

        return response()->json(['data' => $genres]);
    }

    /**
     * GET /api/albums/meta/stats
     */
    public function stats(): JsonResponse
    {
        $stats = Cache::remember('api_albums_stats', 120, fn () => [
            'total'         => Album::count(),
            'avg_rating'    => round(Album::avg('rating'), 2),
            'earliest_year' => Album::min('release_year'),
            'latest_year'   => Album::max('release_year'),
            'genres'        => Album::distinct()->count('genre'),
        ]);

        return response()->json(['data' => $stats]);
    }
}