<?php

namespace App\Console\Commands;

use App\Models\Property;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ExportLocalProperties extends Command
{
    protected $signature = 'propertyme:export-properties';
    protected $description = 'Bundle the current local property database and galleries for deployment through Git';

    public function handle(): int
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->error('Export the snapshot from the local installation.');
            return self::FAILURE;
        }

        $directory = database_path('content/local-properties');
        File::ensureDirectoryExists($directory.'/media');
        $properties = DB::table('properties')->orderBy('id')->get();
        if ($properties->isEmpty()) {
            $this->error('There are no local properties to export.');
            return self::FAILURE;
        }

        $ids = $properties->pluck('id');
        $pivots = DB::table('amenity_property')->whereIn('property_id', $ids)->orderBy('amenity_id')->get();
        $snapshot = [
            'format' => 1,
            'references' => [
                'property_types' => DB::table('property_types')->whereIn('id', $properties->pluck('property_type_id'))->orderBy('id')->get()->all(),
                'locations' => DB::table('locations')->whereIn('id', $properties->pluck('location_id'))->orderBy('id')->get()->all(),
                'amenities' => DB::table('amenities')->whereIn('id', $pivots->pluck('amenity_id'))->orderBy('id')->get()->all(),
            ],
            'properties' => [],
            'media' => [],
        ];

        foreach ($properties as $property) {
            $images = DB::table('property_images')->where('property_id', $property->id)->orderBy('id')->get();
            foreach ($images as $image) {
                if (! preg_match('~^images/[a-f0-9-]{36}\.webp$~D', $image->image_path)) {
                    throw new \RuntimeException('Unexpected property image path.');
                }
                $bytes = Storage::disk('public')->get($image->image_path);
                if (! is_string($bytes)) {
                    throw new \RuntimeException('Missing property image: '.$image->image_path);
                }
                $hash = hash('sha256', $bytes);
                File::put($directory.'/media/'.$hash.'.webp', $bytes);
                $snapshot['media'][$image->image_path] = $hash;
            }
            $snapshot['properties'][] = [
                'property' => $property,
                'images' => $images->all(),
                'amenities' => $pivots->where('property_id', $property->id)->pluck('amenity_id')->values()->all(),
                'translations' => DB::table('content_translations')->where('translatable_type', (new Property)->getMorphClass())->where('translatable_id', $property->id)->orderBy('locale')->get()->all(),
            ];
        }

        $json = json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
        File::replace($directory.'/snapshot.json', $json);
        File::replace($directory.'/version.txt', hash('sha256', $json)."\n");
        $this->info(count($snapshot['properties']).' local properties and '.count($snapshot['media']).' gallery files bundled for Git deployment.');
        return self::SUCCESS;
    }
}
