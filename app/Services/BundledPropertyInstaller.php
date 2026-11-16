<?php

namespace App\Services;

use App\Models\Property;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class BundledPropertyInstaller
{
    public const VERSION_KEY = 'deployment.local_properties_version';
    public const PREVIEW_KEY = 'demo.local_properties_enabled';

    public function __construct(private Settings $settings) {}

    public function install(?string $directory = null): void
    {
        $directory ??= database_path('content/local-properties');
        if (! is_file($directory.'/version.txt')) return;
        $version = trim(File::get($directory.'/version.txt'));
        if ($this->settings->get(self::VERSION_KEY) === $version) return;
        if (! preg_match('/^[a-f0-9]{64}$/D', $version)) throw new \RuntimeException('Invalid property snapshot version.');

        $lock = fopen(storage_path('framework/local-properties.lock'), 'c');
        if ($lock === false) throw new \RuntimeException('Cannot open the property deployment lock.');
        try {
            if (! flock($lock, LOCK_EX)) throw new \RuntimeException('Cannot lock the property deployment.');
            $this->settings->forget();
            if ($this->settings->get(self::VERSION_KEY) === $version) return;
            $json = File::get($directory.'/snapshot.json');
            if (! hash_equals($version, hash('sha256', $json))) throw new \RuntimeException('Property snapshot checksum mismatch.');
            $snapshot = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            if (($snapshot['format'] ?? null) !== 1) throw new \RuntimeException('Unsupported property snapshot format.');

            // Copy and verify media before committing the version. A failed copy is retried next time.
            foreach ($snapshot['media'] as $path => $hash) {
                if (! preg_match('~^images/[a-f0-9-]{36}\.webp$~D', $path) || ! preg_match('/^[a-f0-9]{64}$/D', $hash)) {
                    throw new \RuntimeException('Invalid bundled property image path.');
                }
                $source = $directory.'/media/'.$hash.'.webp';
                if (! is_file($source) || ! hash_equals($hash, hash_file('sha256', $source))) {
                    throw new \RuntimeException('Bundled property image checksum mismatch.');
                }
                if (! Storage::disk('public')->exists($path)) {
                    $temporary = $path.'.installing';
                    if (! Storage::disk('public')->put($temporary, File::get($source)) || ! Storage::disk('public')->move($temporary, $path)) {
                        throw new \RuntimeException('Cannot install property image: '.$path);
                    }
                }
            }

            DB::table('website_settings')->insertOrIgnore(['key' => self::VERSION_KEY, 'value' => '', 'type' => 'text']);
            DB::transaction(function () use ($snapshot, $version) {
                $state = DB::table('website_settings')->where('key', self::VERSION_KEY)->lockForUpdate()->first();
                if ($state->value === $version) return;
                $references = [];
                foreach (['property_types' => 'slug', 'locations' => 'slug', 'amenities' => 'name'] as $table => $key) {
                    foreach ($snapshot['references'][$table] as $record) {
                        $sourceId = $record['id'];
                        unset($record['id']);
                        $id = DB::table($table)->where($key, $record[$key])->orderBy('id')->value('id');
                        $references[$table][$sourceId] = $id ?? DB::table($table)->insertGetId($record);
                    }
                }

                foreach ($snapshot['properties'] as $entry) {
                    $property = $entry['property'];
                    // Include soft-deleted rows: a deployment must not recreate an editor's deletion.
                    if (DB::table('properties')->where('property_code', $property['property_code'])->orWhere('slug', $property['slug'])->exists()) continue;
                    unset($property['id']);
                    $property['property_type_id'] = $references['property_types'][$property['property_type_id']];
                    $property['location_id'] = $references['locations'][$property['location_id']];
                    $id = DB::table('properties')->insertGetId($property);
                    foreach ($entry['images'] as $image) {
                        unset($image['id']);
                        $image['property_id'] = $id;
                        DB::table('property_images')->insert($image);
                    }
                    foreach ($entry['amenities'] as $amenity) {
                        DB::table('amenity_property')->insert(['property_id' => $id, 'amenity_id' => $references['amenities'][$amenity]]);
                    }
                    foreach ($entry['translations'] as $translation) {
                        unset($translation['id']);
                        $translation['translatable_type'] = (new Property)->getMorphClass();
                        $translation['translatable_id'] = $id;
                        DB::table('content_translations')->insert($translation);
                    }
                }

                // A database setting takes effect even when the hosting config is cached.
                DB::table('website_settings')->insertOrIgnore(['key' => self::PREVIEW_KEY, 'value' => '1', 'type' => 'boolean']);
                DB::table('website_settings')->where('key', self::VERSION_KEY)->update(['value' => $version, 'updated_at' => now()]);
            });
            $this->settings->forget();
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}
