<?php

namespace App\Http\Controllers;

use App\Models\Translation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class TranslationController
 *
 * Manages CRUD operations and search/export endpoints for translations.
 *
 * @package App\Http\Controllers
 */
class TranslationController extends Controller
{
    /**
     * Display the specified translation.
     *
     * @param int $id Translation ID.
     * @return JsonResponse Returns the translation data in JSON format.
     */
    public function show(int $id): JsonResponse
    {
        $translation = Translation::findOrFail($id);
        return response()->json($translation);
    }

    /**
     * Store a newly created translation.
     *
     * @param Request $request Incoming request data.
     * @return JsonResponse Returns the created translation data in JSON format.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'locale' => 'required|string',
            'key'    => 'required|string|unique:translations,key,NULL,id,locale,' . $request->input('locale'),
            'value'  => 'required|string',
            'tags'   => 'nullable|array',
        ]);

        $translation = Translation::create($data);
        return response()->json($translation, 201);
    }

    /**
     * Update the specified translation.
     *
     * @param Request $request Incoming request data.
     * @param int $id Translation ID.
     * @return JsonResponse Returns the updated translation data.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $translation = Translation::findOrFail($id);
        $data = $request->validate([
            'locale' => 'sometimes|required|string',
            'key'    => 'sometimes|required|string|unique:translations,key,' . $id . ',id,locale,' . $request->input('locale', $translation->locale),
            'value'  => 'sometimes|required|string',
            'tags'   => 'nullable|array',
        ]);

        $translation->update($data);
        return response()->json($translation);
    }

    /**
     * Remove the specified translation.
     *
     * @param int $id Translation ID.
     * @return JsonResponse Returns a success message.
     */
    public function destroy(int $id): JsonResponse
    {
        $translation = Translation::findOrFail($id);
        $translation->delete();
        return response()->json(['message' => 'Translation deleted successfully']);
    }

    /**
     * Search for translations by key, content, or tags.
     *
     * @param Request $request Incoming search parameters.
     * @return JsonResponse Returns paginated search results.
     */
    public function search(Request $request): JsonResponse
    {
        $query = Translation::query();

        if ($request->filled('key')) {
            $query->where('key', 'like', '%' . $request->input('key') . '%');
        }

        if ($request->filled('value')) {
            $query->where('value', 'like', '%' . $request->input('value') . '%');
        }

        if ($request->filled('tags')) {
            // Expect tags as comma-separated values
            $tagsArray = array_map('trim', explode(',', $request->input('tags')));
            $query->where(function ($q) use ($tagsArray) {
                foreach ($tagsArray as $tag) {
                    $q->orWhereJsonContains('tags', $tag);
                }
            });
        }

        if ($request->filled('locale')) {
            $query->where('locale', $request->input('locale'));
        }

        // Paginate results to ensure fast response times (20 per page)
        $results = $query->paginate(20);
        return response()->json($results);
    }

    /**
     * Export all translations as JSON.
     *
     * Uses chunking in case of huge datasets. In production, consider streaming the response.
     *
     * @return JsonResponse Returns all translations in JSON format.
     */
    public function export(): JsonResponse
    {
        // For large datasets, replace with chunking or streaming responses
        $allTranslations = Translation::all();
        return response()->json($allTranslations);
    }
}
