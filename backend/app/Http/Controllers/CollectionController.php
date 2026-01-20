<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CollectionController extends Controller
{
    public function index()
    {
        $collections = auth()->user()->collections()
            ->withCount('recipes')
            ->get();

        return Inertia::render('Collections/Index', [
            'collections' => $collections
        ]);
    }

    public function show(Collection $collection)
    {
        $this->authorize('view', $collection);

        $collection->load('recipes');

        return Inertia::render('Collections/Show', [
            'collection' => $collection
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        $collection = auth()->user()->collections()->create($request->all());

        return redirect()->route('collections.show', $collection->id)->with('success', 'Collection created.');
    }

    public function update(Request $request, Collection $collection)
    {
        $this->authorize('update', $collection);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        $collection->update($request->all());

        return redirect()->back()->with('success', 'Collection updated.');
    }

    public function addRecipe(Request $request, Collection $collection)
    {
        $this->authorize('update', $collection);

        $request->validate([
            'recipe_id' => 'required|exists:recipes,id'
        ]);

        $collection->recipes()->syncWithoutDetaching([$request->recipe_id]);

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
}
