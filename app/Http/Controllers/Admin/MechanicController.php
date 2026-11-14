<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mechanic;
use Illuminate\Http\Request;

class MechanicController extends Controller
{
    public function index()
    {
        $mechanics = Mechanic::withCount(['bookings as active_jobs' => fn ($q) => $q->where('status', 'in_service')])
            ->orderBy('name')
            ->get();

        return view('admin.mechanics.index', compact('mechanics'));
    }

    public function create()
    {
        return view('admin.mechanics.form', ['mechanic' => new Mechanic(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        Mechanic::create($this->validated($request));

        return redirect()->route('admin.mechanics.index')->with('success', 'Mekanik ditambahkan.');
    }

    public function edit(Mechanic $mechanic)
    {
        return view('admin.mechanics.form', compact('mechanic'));
    }

    public function update(Request $request, Mechanic $mechanic)
    {
        $mechanic->update($this->validated($request));

        return redirect()->route('admin.mechanics.index')->with('success', 'Data mekanik diperbarui.');
    }

    public function destroy(Mechanic $mechanic)
    {
        $mechanic->delete();

        return back()->with('success', 'Mekanik dihapus.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'specialty' => ['nullable', 'string', 'max:100'],
        ]);

        return $data + ['is_active' => $request->boolean('is_active')];
    }
}
