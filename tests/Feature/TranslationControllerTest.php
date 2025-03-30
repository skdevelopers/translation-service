<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Translation;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class TranslationControllerTest
 *
 * This test suite validates the functionality of the TranslationController endpoints.
 *
 * It covers:
 * - Retrieval of a single translation (show endpoint)
 * - Creation of a new translation (store endpoint)
 * - Updating an existing translation (update endpoint)
 * - Deletion of a translation (delete endpoint)
 * - Searching translations based on specific criteria (search endpoint)
 * - Exporting all translations (export endpoint), including performance testing with 100,000 records.
 *
 * @package Tests\Feature
 */
class TranslationControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Authenticate a user for the test.
     *
     * @return \App\Models\User The authenticated user.
     */
    protected function authenticateUser(): User
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        return $user;
    }

    /**
     * Test that the show endpoint returns the correct translation.
     *
     * @return void
     */
    public function testShowTranslation(): void
    {
        $this->authenticateUser();
        $translation = Translation::factory()->create();
        $response = $this->getJson('/api/translations/' . $translation->id);
        $response->assertStatus(200)
            ->assertJson([
                'id'     => $translation->id,
                'locale' => $translation->locale,
                'key'    => $translation->key,
                'value'  => $translation->value,
            ]);
    }

    /**
     * Test that storing a new translation works as expected.
     *
     * @return void
     */
    public function testStoreTranslation(): void
    {
        $this->authenticateUser();

        $data = [
            'locale' => 'en',
            'key'    => 'greeting',
            'value'  => 'Hello world',
            'tags'   => ['mobile', 'web'],
        ];

        $response = $this->postJson('/api/translations', $data);
        $response->assertStatus(201)
            ->assertJsonFragment(['key' => 'greeting']);
    }

    /**
     * Test that updating an existing translation works correctly.
     *
     * @return void
     */
    public function testUpdateTranslation(): void
    {
        $this->authenticateUser();

        $translation = Translation::factory()->create([
            'locale' => 'en',
            'key'    => 'farewell',
            'value'  => 'Goodbye',
        ]);

        $updateData = ['value' => 'Goodbye everyone'];
        $response = $this->putJson('/api/translations/' . $translation->id, $updateData);
        $response->assertStatus(200)
            ->assertJsonFragment(['value' => 'Goodbye everyone']);
    }

    /**
     * Test that deleting a translation returns the appropriate message.
     *
     * @return void
     */
    public function testDeleteTranslation(): void
    {
        $this->authenticateUser();

        $translation = Translation::factory()->create();
        $response = $this->deleteJson('/api/translations/' . $translation->id);
        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Translation deleted successfully']);
    }

    /**
     * Test the search endpoint for translations.
     *
     * This test verifies that searching by key returns the expected translation.
     *
     * @return void
     */
    public function testSearchTranslations(): void
    {
        $this->authenticateUser();

        Translation::factory()->create([
            'locale' => 'en',
            'key'    => 'searchkey',
            'value'  => 'Test search value',
            'tags'   => ['mobile'],
        ]);

        $response = $this->getJson('/api/translations/search?key=searchkey');
        $response->assertStatus(200)
            ->assertJsonFragment(['key' => 'searchkey']);
    }

    /**
     * Test that the export endpoint returns all translations efficiently.
     *
     * This test seeds the database with 100,000 records and measures the time taken
     * by the export endpoint to generate and serve the JSON file. It asserts that the
     * response status is 200 and that the export completes in less than 0.5 seconds.
     * It also verifies that the exported file contains a valid JSON array.
     *
     * @return void
     */
    public function testExportPerformance(): void
    {
        $this->authenticateUser();

        // Seed a larger dataset (100,000 records for performance testing)
        Translation::factory()->count(100000)->create();

        $start = microtime(true);
        $response = $this->get('/api/translations/export');
        $duration = microtime(true) - $start;

        // Assert that the response is successful and the duration is less than 0.5 seconds.
        $response->assertStatus(200);
        $this->assertLessThan(0.5, $duration, "Export endpoint took {$duration} seconds, exceeding 0.5 seconds.");

        // Get the exported file from the response.
        $file = $response->getFile();
        $this->assertNotNull($file, "Exported file is not available.");

        // Read the content of the file.
        $content = file_get_contents($file->getPathname());
        $data = json_decode($content, true);

        // Ensure that decoding was successful and the file contains an array.
        $this->assertIsArray($data);
    }

    /**
     * Test that the export endpoint returns exactly 10 translations.
     *
     * This test ensures that when 10 translations are created, the export endpoint
     * returns a file which, when read and decoded, contains exactly 10 records.
     *
     * @return void
     */
    public function testExportTranslations(): void
    {
        $this->authenticateUser();

        // Create 10 translations.
        Translation::factory()->count(10)->create();

        // Perform a GET request to the export endpoint.
        $response = $this->get('/api/translations/export');
        $response->assertStatus(200);

        // Get the exported file from the response.
        $file = $response->getFile();
        $this->assertNotNull($file, "Exported file is not available.");

        // Read the content of the file.
        $content = file_get_contents($file->getPathname());
        $data = json_decode($content, true);

        // Ensure that decoding was successful and exactly 10 records were exported.
        $this->assertIsArray($data);
        $this->assertCount(10, $data);
    }
}
