<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\PropertyRequest;
use App\Models\{Property, Amenity};
use App\Services\{MediaService, Audit};
use App\Support\Resources;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Gate};
class PropertyController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Property::class);
        $query = Property::with(['location', 'propertyType']);
        if ($q = $request->string('q')->limit(100)->toString()) { $query->where(fn ($q2) => $q2->where('title', 'like', '%'.$q.'%')->orWhere('property_code', 'like', '%'.$q.'%')); }
        return view('admin.index', ['resource' => 'properties', 'title' => 'Properties', 'column' => 'title', 'records' => $query->latest()->paginate(15)->withQueryString()]);
    }
    public function create() { Gate::authorize('create', Property::class); return $this->form(new Property(['currency' => 'IDR', 'show_price' => true, 'status' => 'available', 'publication_status' => 'draft'])); }
    public function edit(Property $property) { Gate::authorize('update', $property); return $this->form($property->load(['images', 'amenities'])); }
    protected function form(Property $property) { return view('admin.form', ['resource' => 'properties', 'title' => 'Properties', 'record' => $property, 'fields' => Resources::propertyFields(), 'amenities' => Amenity::orderBy('name')->get()]); }
    public function store(PropertyRequest $request, MediaService $media) { return $this->save($request, $media, new Property); }
    public function update(PropertyRequest $request, MediaService $media, Property $property) { return $this->save($request, $media, $property); }
    private function save(PropertyRequest $request, MediaService $media, Property $property)
    {
        Gate::authorize($property->exists ? 'update' : 'create', $property->exists ? $property : Property::class); $data = $request->validated();
        DB::transaction(function () use ($property, $data, $media) {
            $new = ! $property->exists;
            $property->fill(collect($data)->except(['amenities', 'gallery', 'images', 'primary_image', 'remove_images', 'translations'])->all());
            if ($property->publication_status === 'published' && ! $property->published_at) { $property->published_at = now(); }
            $property->save(); $property->amenities()->sync($data['amenities'] ?? []); $media->syncGallery($property, $data);
            if (array_key_exists('translations',$data)) $property->saveTranslation($data['translations'] ?? []);
            Audit::record($new ? 'created' : 'updated', $property);
        });
        return redirect()->route('admin.properties.edit', $property)->with('success', 'Property saved successfully.');
    }
    public function destroy(Property $property) { Gate::authorize('delete', $property); DB::transaction(function () use ($property) { Audit::record('deleted', $property); $property->delete(); }); return redirect()->route('admin.properties.index')->with('success', 'Property removed from the website.'); }
}
