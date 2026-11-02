<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProtocolResource;
use App\Models\Protocol;
use Inertia\Inertia;

class ProtocolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $protocols = Protocol::with('uploader', 'editor')
            ->orderByDesc('date')
            ->get();

        return Inertia::render('Protocols/Index', [
            'protocols' => ProtocolResource::collection($protocols),
        ]);
    }
}
