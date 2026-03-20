<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AlbumController extends Controller
{
    /**
     * Browse page — shows all albums with filters.
     */
    public function index(Request $request)
    {
        $query = Album::with('user:id,name')
            ->when($request->search, fn ($q) =>
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('artist', 'like', "%{$request->search}%")
            )
            ->when($request->genre, fn ($q) =>
                $q->where('genre', $request->genre)
            )
            ->when($request->rating, fn ($q) =>
                $q->where('rating', $request->rating)
            );

        $sortField = in_array($request->sort, ['title', 'artist', 'release_year', 'rating'])
            ? $request->sort : 'created_at';
        $sortDir = $request->dir === 'asc' ? 'asc' : 'desc';

        $albums = $query->orderBy($sortField, $sortDir)->get();
        $genres = Album::distinct()->orderBy('genre')->pluck('genre');

        return Inertia::render('Music/Index', [
            'albums'  => $albums,
            'genres'  => $genres,
            'filters' => $request->only(['search', 'genre', 'rating', 'sort', 'dir']),
        ]);
    }

    /**
     * Show add form.
     */
    public function create()
    {
        return Inertia::render('Music/Create');
    }

    /**
     * Store new album.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'artist'       => 'required|string|max:255',
            'release_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'genre'        => 'required|string|max:100',
            'rating'       => 'required|integer|min:1|max:5',
            'description'  => 'required|string',
            'image'        => 'nullable|url|max:500',
        ]);

        $data['user_id'] = Auth::id();

        Album::create($data);

        return redirect()->route('music.index')->with('success', 'Album lisatud!');
    }

    /**
     * Show edit form — only owner or admin.
     */
    public function edit(Album $album)
    {
        $this->authorizeAccess($album);

        return Inertia::render('Music/Edit', [
            'album' => $album,
        ]);
    }

    /**
     * Update album — only owner or admin.
     */
    public function update(Request $request, Album $album)
    {
        $this->authorizeAccess($album);

        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'artist'       => 'required|string|max:255',
            'release_year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'genre'        => 'required|string|max:100',
            'rating'       => 'required|integer|min:1|max:5',
            'description'  => 'required|string',
            'image'        => 'nullable|url|max:500',
        ]);

        $album->update($data);

        return redirect()->route('music.index')->with('success', 'Album uuendatud!');
    }

    /**
     * Delete album — only owner or admin.
     */
    public function destroy(Album $album)
    {
        $this->authorizeAccess($album);
        $album->delete();

        return redirect()->route('music.index')->with('success', 'Album kustutatud!');
    }

    // -------------------------------------------------------------------------

    private function authorizeAccess(Album $album): void
    {
        $user = Auth::user();
        if ($user->isAdmin()) return;
        abort_if($album->user_id !== $user->id, 403, 'Puudub õigus.');
    }
}