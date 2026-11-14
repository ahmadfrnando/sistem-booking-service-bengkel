<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $services = Service::active()->orderBy('id')->take(6)->get();

        return view('home', compact('services'));
    }

    public function services(Request $request)
    {
        $types = config('bengkel.vehicle_types');
        $type = array_key_exists($request->query('type'), $types) ? $request->query('type') : null;

        $services = Service::active()
            ->when($type, fn ($query) => $query->availableFor($type))
            ->orderBy('category')
            ->orderBy('id')
            ->get()
            ->groupBy('category');

        return view('services', compact('services', 'types', 'type'));
    }
}
