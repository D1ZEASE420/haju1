<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function dashboard()
{
    $markers = Marker::all();
    return Inertia::render('Dashboard', [
        'markers' => $markers,
    ]);
}
}
