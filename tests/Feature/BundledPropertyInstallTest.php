<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Services\{BundledPropertyInstaller, Settings};
use Illuminate\Support\Facades\{DB, File, Storage};
use Tests\PropertymeTestCase;

class BundledPropertyInstallTest extends PropertymeTestCase
{
    public function test_first_production_http_request_installs_the_local_snapshot_without_environment_changes(): void
    {
        Storage::fake('public');
        $this->app['env'] = 'production';
        config(['propertyme.demo_content_enabled' => false]);
        // Emulate the HTTP runtime while executing this request under PHPUnit's CLI.
        (new \ReflectionProperty($this->app, 'isRunningInConsole'))->setValue($this->app, false);

        $this->get('/properties')->assertOk()->assertSee('Palm Courtyard Villa')->assertSee('Demo content.')->assertSee('noindex,follow');
        $this->assertDatabaseCount('properties', 12);
        $this->assertDatabaseCount('property_images', 30);
        $this->assertSame(12, Property::published()->count());
        $snapshot = json_decode(File::get(database_path('content/local-properties/snapshot.json')), true);
        foreach ($snapshot['properties'] as $entry) {
            $property = DB::table('properties')->where('property_code', $entry['property']['property_code'])->first();
            foreach ($entry['property'] as $field => $value) {
                if (! in_array($field, ['id', 'property_type_id', 'location_id'], true)) {
                    $this->assertEquals($value, $property->$field, $property->property_code.'.'.$field);
                }
            }
            $this->assertSame(count($entry['amenities']), DB::table('amenity_property')->where('property_id', $property->id)->count());
            $translation = DB::table('content_translations')->where('translatable_type', Property::class)->where('translatable_id', $property->id)->where('locale', 'id')->first();
            $this->assertEquals(json_decode($entry['translations'][0]['fields'], true), json_decode($translation->fields, true));
        }
        foreach ($snapshot['media'] as $path => $hash) {
            $this->assertSame($hash, hash('sha256', Storage::disk('public')->get($path)));
        }
        $this->get('/properties/demo-palm-courtyard-villa')->assertOk();
        $this->get('/sitemap.xml')->assertOk()->assertDontSee('/properties/demo-');
        $this->get('/properties')->assertOk();
        $this->assertDatabaseCount('properties', 12);
        $this->assertDatabaseCount('property_images', 30);
    }

    public function test_new_versions_preserve_hosting_edits_deletions_and_colliding_slugs(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $directory = Storage::disk('local')->path('snapshot');
        File::copyDirectory(database_path('content/local-properties'), $directory);
        $snapshot = json_decode(File::get($directory.'/snapshot.json'), true);
        $existing = $snapshot['properties'][0]['property'];
        unset($existing['id']);
        $existing['property_code'] = 'HOSTING-ORIGINAL';
        $existing['title'] = 'Existing hosting property';
        $existing['property_type_id'] = DB::table('property_types')->where('slug', 'villa')->value('id');
        $existing['location_id'] = DB::table('locations')->where('slug', 'canggu')->value('id');
        DB::table('properties')->insert($existing);

        $installer = app(BundledPropertyInstaller::class);
        $installer->install($directory);
        $this->assertDatabaseCount('properties', 12);
        $this->assertDatabaseMissing('properties', ['property_code' => 'DEMO-PM-001']);
        Property::where('property_code', 'DEMO-PM-002')->firstOrFail()->update(['title' => 'Edited on hosting']);
        Property::where('property_code', 'DEMO-PM-003')->firstOrFail()->delete();
        $new = $snapshot['properties'][0];
        $new['property']['property_code'] = 'LOCAL-NEW';
        $new['property']['slug'] = 'local-new';
        $snapshot['properties'][] = $new;
        $json = json_encode($snapshot, JSON_THROW_ON_ERROR);
        File::put($directory.'/snapshot.json', $json);
        File::put($directory.'/version.txt', hash('sha256', $json));
        $installer->install($directory);
        $installer->install($directory);

        $this->assertDatabaseCount('properties', 13);
        $this->assertDatabaseHas('properties', ['property_code' => 'HOSTING-ORIGINAL', 'title' => 'Existing hosting property']);
        $this->assertDatabaseHas('properties', ['property_code' => 'DEMO-PM-002', 'title' => 'Edited on hosting']);
        $this->assertSoftDeleted('properties', ['property_code' => 'DEMO-PM-003']);
        $this->assertDatabaseHas('properties', ['property_code' => 'LOCAL-NEW']);
        $this->assertDatabaseCount('property_images', 30);
        $this->assertSame(hash('sha256', $json), app(Settings::class)->get(BundledPropertyInstaller::VERSION_KEY));
    }

    public function test_broken_media_is_not_marked_installed_and_the_next_attempt_can_retry(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $directory = Storage::disk('local')->path('snapshot');
        File::copyDirectory(database_path('content/local-properties'), $directory);
        $snapshot = json_decode(File::get($directory.'/snapshot.json'), true);
        $hash = reset($snapshot['media']);
        File::put($directory.'/media/'.$hash.'.webp', 'broken image');
        $installer = app(BundledPropertyInstaller::class);
        try {
            $installer->install($directory);
            $this->fail('Corrupt media must abort installation.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Bundled property image checksum mismatch.', $exception->getMessage());
        }
        $this->assertDatabaseCount('properties', 0);
        $this->assertNull(DB::table('website_settings')->where('key', BundledPropertyInstaller::VERSION_KEY)->value('value'));
        File::copy(database_path('content/local-properties/media/'.$hash.'.webp'), $directory.'/media/'.$hash.'.webp');
        $installer->install($directory);
        $this->assertDatabaseCount('properties', 12);
        $this->assertDatabaseCount('property_images', 30);
    }
}
