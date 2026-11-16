<?php
namespace App\Services;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class MediaService
{
    public const RULES = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192', 'dimensions:max_width=6000,max_height=6000'];
    public function store(UploadedFile $file): string
    {
        [$width, $height] = getimagesize($file->getRealPath());
        if ($width * $height > 16000000) { throw ValidationException::withMessages(['gallery' => 'Images must contain no more than 16 megapixels.']); }
        $source = @imagecreatefromstring(file_get_contents($file->getRealPath()));
        if (! $source) { throw ValidationException::withMessages(['gallery' => 'The image could not be decoded.']); }
        $scale = min(1, 2200 / max($width, $height));
        $image = imagescale($source, max(1, (int) ($width * $scale)), max(1, (int) ($height * $scale)));
        imagealphablending($image, false); imagesavealpha($image, true);
        ob_start(); imagewebp($image, null, 84); $bytes = ob_get_clean();
        $path = 'images/'.Str::uuid().'.webp'; Storage::disk('public')->put($path, $bytes);
        return $path;
    }
    public function syncGallery($model, array $data): void
    {
        foreach ($data['gallery'] ?? [] as $upload) { $model->images()->create(['image_path' => $this->store($upload), 'alt_text' => $model->title, 'sort_order' => $model->images()->count()]); }
        foreach ($data['images'] ?? [] as $id => $attributes) { $model->images()->whereKey($id)->update(collect($attributes)->only(['alt_text', 'sort_order'])->all()); }
        foreach ($model->images()->whereIn('id', $data['remove_images'] ?? [])->get() as $image) { $image->delete(); Storage::disk('public')->delete($image->image_path); }
        if ($model instanceof \App\Models\Property) {
            $primary = $model->images()->whereKey($data['primary_image'] ?? null)->first() ?? $model->images()->first();
            $model->images()->update(['is_primary' => false]); $primary?->update(['is_primary' => true]);
        }
    }
}
