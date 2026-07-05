<?php

namespace App\Http\Controllers;

use App\Models\Storyline;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StorylineController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $validated['user_id'] = auth()->id();
        Storyline::create($validated);

        return back()->with('toast', 'Storyline narrative arc initiated!');
    }
}
