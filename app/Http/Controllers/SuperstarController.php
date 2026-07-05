<?php

namespace App\Http\Controllers;

use App\Models\Show;
use App\Models\Superstar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuperstarController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'required|string|in:Male,Female',
            'show_id' => 'required|exists:shows,id',
            'image' => 'nullable|string',
        ]);

        Superstar::create($validated);

        return back()->with('toast', 'Competitor registered successfully!');
    }

    public function update(Request $request, Superstar $superstar): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'required|string|in:Male,Female',
            'show_id' => 'required|exists:shows,id',
            'wins' => 'required|integer|min:0',
            'losses' => 'required|integer|min:0',
            'draws' => 'required|integer|min:0',
            'image' => 'nullable|string',
        ]);

        $superstar->update($validated);

        return back()->with('toast', 'Competitor updated successfully!');
    }

    public function destroy(Superstar $superstar): RedirectResponse
    {
        $superstar->delete();

        return back()->with('toast', 'Competitor expunged successfully!');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'superstars' => 'required|array',
            'superstars.*.name' => 'required|string|max:255',
            'superstars.*.gender' => 'nullable|string',
            'superstars.*.brand' => 'nullable|string',
        ]);

        $superstarsData = $request->input('superstars');

        if (empty($superstarsData)) {
            return back()->with('toast', 'No data to import.');
        }

        $shows = Show::all();
        if ($shows->isEmpty()) {
            return back()->with('toast', 'Configure at least one show before importing roster.');
        }

        DB::transaction(function () use ($superstarsData, $shows) {
            foreach ($superstarsData as $data) {
                $gender = trim($data['gender'] ?? 'Male');
                if (! in_array($gender, ['Male', 'Female'])) {
                    $gender = 'Male';
                }
                $brandStr = trim($data['brand'] ?? '');

                $matchedShowId = null;
                if ($brandStr) {
                    $matchedShow = $shows->first(fn ($s) => stripos($s->name, $brandStr) !== false);
                    $matchedShowId = $matchedShow ? $matchedShow->id : $shows->first()->id;
                } else {
                    $matchedShowId = $shows->first()->id;
                }

                Superstar::create([
                    'name' => trim($data['name']),
                    'gender' => $gender,
                    'show_id' => $matchedShowId,
                    'wins' => 0,
                    'losses' => 0,
                    'draws' => 0,
                ]);
            }
        });

        return back()->with('toast', 'Superstars batch imported successfully!');
    }
}
