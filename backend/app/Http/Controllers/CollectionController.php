<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class CollectionController extends Controller
{
    public function index()
    {
        $collections = auth()->user()->collections()
            ->withCount('recipes')
            ->latest()
            ->get();

        return Inertia::render('Collections/Index', [
            'collections' => $collections
        ]);
    }

    public function show(Collection $collection)
    {
        $this->authorize('view', $collection);

        $collection->load(['recipes' => function($query) {
            $query->latest()->paginate(12);
        }]);

        return Inertia::render('Collections/Show', [
            'collection' => $collection
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_public' => 'boolean',
        ]);

        $collection = auth()->user()->collections()->create([
            'name' => $request->name,
            'description' => $request->description,
            'is_public' => $request->is_public ?? false,
        ]);

        return redirect()->route('collections.show', $collection->id)->with('success', 'Collection created.');
    }

    public function update(Request $request, Collection $collection)
    {
        $this->authorize('update', $collection);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_public' => 'boolean',
        ]);

        $collection->update($request->only(['name', 'description', 'is_public']));

        return redirect()->back()->with('success', 'Collection updated.');
    }

    public function addRecipe(Request $request, Collection $collection)
    {
        $this->authorize('update', $collection);

        $request->validate([
            'recipe_id' => 'required|exists:recipes,id'
        ]);

        // Get current max position
        $maxPosition = $collection->recipes()->max('collection_recipe.position') ?? 0;

        $collection->recipes()->syncWithoutDetaching([
            $request->recipe_id => ['position' => $maxPosition + 1]
        ]);

        return redirect()->back()->with('success', 'Recipe added to collection.');
    }

    public function removeRecipe(Collection $collection, Recipe $recipe)
    {
        $this->authorize('update', $collection);

        $collection->recipes()->detach($recipe->id);

        return redirect()->back()->with('success', 'Recipe removed from collection.');
    }

    public function destroy(Collection $collection)
    {
        $this->authorize('delete', $collection);

        $collection->delete();

        return redirect()->route('collections.index')->with('success', 'Collection deleted.');
    }

    /**
     * Export collection as PDF cookbook.
     */
    public function exportPdf(Collection $collection)
    {
        $this->authorize('view', $collection);

        $collection->load('recipes');

        try {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('collections.pdf', [
                'collection' => $collection,
            ]);

            $filename = \Illuminate\Support\Str::slug($collection->name) . '-cookbook.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Collection PDF export error: ' . $e->getMessage());

            return back()->withErrors(['error' => 'Failed to export collection. Please try again.']);
        }
    }
}
