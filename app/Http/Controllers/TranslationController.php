<?php

namespace App\Http\Controllers;

use App\Models\Translation;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            // Expect tags as comma-separated values.
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

        // Paginate results (20 per page) for fast response times.
        $results = $query->paginate(20);
        return response()->json($results);
    }

    /**
     * Export all translations as a JSON response using MySQL JSON aggregation.
     *
     * This method checks for a cached export. If none exists, it uses a raw SQL query
     * with JSON_OBJECT and JSON_ARRAYAGG to aggregate translations into a JSON array,
     * caches the result for 5 minutes, and returns the JSON.
     *
     * Note: The first request (cache miss) may exceed the 500ms threshold. However,
     * subsequent requests (cache hits) will be very fast.
     *
     * @param Request $request The incoming request.
     * @return BinaryFileResponse Returns a JSON response containing the export.
     */
    public function export(Request $request): BinaryFileResponse
    {
        return Cache::remember('translations_export', now()->addMinutes(5), function () {
            // Use chunked JSON building for better memory management
            $path = storage_path('app/translations_export.json');

            File::put($path, '[');

            Translation::chunk(5000, function ($translations) use ($path) {
                $jsonChunk = $translations->map(function ($t) {
                    return json_encode([
                        'id' => $t->id,
                        'locale' => $t->locale,
                        'key' => $t->key,
                        'value' => $t->value,
                        'tags' => $t->tags
                    ]);
                })->implode(',');

                File::append($path, $jsonChunk);
            });

            File::append($path, ']');

            return response()->file($path, [
                'Content-Type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="translations.json"'
            ]);
        });
    }
}
