<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSpaceRequest;
use App\Http\Requests\UpdateSpaceRequest;
use App\Models\Space;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SpaceController extends Controller
{
    /**
     * Display a listing of spaces (scoped to current company by trait).
     */
    public function index(): Response
    {
        return Inertia::render('Spaces/Index', [
            'spaces' => Space::orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for creating a new space.
     */
    public function create(): Response
    {
        return Inertia::render('Spaces/Create');
    }

    /**
     * Store a newly created space.
     */
    public function store(StoreSpaceRequest $request): RedirectResponse
    {
        Space::create($request->validated());

        return redirect()
            ->route('spaces.index')
            ->with('success', 'Espacio creado correctamente.');
    }

    /**
     * Show form for editing a space.
     */
    public function edit(Space $space): Response
    {
        return Inertia::render('Spaces/Edit', [
            'space' => $space,
        ]);
    }

    /**
     * Update a space.
     */
    public function update(UpdateSpaceRequest $request, Space $space): RedirectResponse
    {
        $space->update($request->validated());

        return redirect()
            ->route('spaces.index')
            ->with('success', 'Espacio actualizado.');
    }

    /**
     * Delete a space.
     */
    public function destroy(Space $space): RedirectResponse
    {
        $space->delete();

        return redirect()
            ->route('spaces.index')
            ->with('success', 'Espacio eliminado.');
    }
}
