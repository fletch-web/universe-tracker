<?php

namespace App\Http\Controllers;

use App\Models\Show;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShowController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'image' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();
        Show::create($validated);

        return back()->with('toast', 'Show initialized successfully!');
    }

    public function update(Request $request, Show $show): RedirectResponse
    {
        if ($show->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'image' => 'nullable|string',
        ]);

        $show->update($validated);

        return back()->with('toast', 'Show updated successfully!');
    }

    public function destroy(Show $show): RedirectResponse
    {
        if ($show->user_id !== auth()->id()) {
            abort(403);
        }

        $show->delete();

        return back()->with('toast', 'Show expunged successfully!');
    }
}
