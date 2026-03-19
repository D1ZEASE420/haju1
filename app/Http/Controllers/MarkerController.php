<?php

// app/Http/Controllers/MarkerController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Marker;

class MarkerController extends Controller
{
    public function index()
    {
        return Marker::all();
    }

    public function store(Request $request)
    {
        $marker = Marker::create($request->only(['name', 'latitude', 'longitude', 'description']));
        return response()->json($marker);
    }

    public function update(Request $request, $id)
    {
        $marker = Marker::findOrFail($id);
        $marker->update($request->only(['name', 'description']));
        return response()->json($marker);
    }

    public function destroy($id)
    {
        $marker = Marker::findOrFail($id);
        $marker->delete();
        return response()->json(['success' => true]);
    }
}