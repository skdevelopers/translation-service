<?php

namespace App\Http\Controllers;

use App\Models\Translation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
     * Export all translations to a JSON file in the public/exports directory.
     *
     * This method writes translations in chunks to a JSON file to minimize memory usage.
     * It first checks if the export directory exists and creates it with proper permissions if needed.
     * Once the file is written, it returns a download response and deletes the file after sending.
     *
     * For large datasets, the file is written in manageable chunks (with periodic flushes) and
     * the filename includes a precise timestamp to ensure uniqueness.
     *
     * @return BinaryFileResponse Returns a response that prompts the user to download the exported file.
     */
    public function export(): BinaryFileResponse
    {
        // Define the export directory and ensure it exists.
        $exportDir = public_path('exports');
        if (!File::exists($exportDir)) {
            File::makeDirectory($exportDir, 0755, true);
        }

        // Generate a unique filename with a precise timestamp.
        $filename = $exportDir . DIRECTORY_SEPARATOR . 'translations_' . date('Ymd_His_u') . '.json';

        // Open a file pointer for writing.
        $handle = fopen($filename, 'w');
        if ($handle === false) {
            return response()->json(['message' => 'Could not open file for writing.'], 500);
        }

        // Start the JSON array.
        fwrite($handle, '[');
        $firstRecord = true;
        $chunkSize = 1000; // Process records in chunks.

        // Process translations in chunks.
        Translation::chunk($chunkSize, function ($translations) use (&$firstRecord, $handle) {
            foreach ($translations as $translation) {
                // Separate records with a comma if this is not the first element.
                if (!$firstRecord) {
                    fwrite($handle, ',');
                } else {
                    $firstRecord = false;
                }
                // Write the JSON encoded translation.
                fwrite($handle, json_encode($translation));
                // Flush output if possible.
                if (function_exists('fflush')) {
                    fflush($handle);
                }
            }
        });

        // End the JSON array.
        fwrite($handle, ']');
        fclose($handle);

        // Return a download response; the file will be deleted after sending.
        return response()->download($filename)->deleteFileAfterSend();
    }
}
