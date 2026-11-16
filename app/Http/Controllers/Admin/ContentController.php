<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContentRequest;
use App\Services\{Audit, MediaService};
use App\Support\Resources;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Gate, Storage};
class ContentController extends Controller
{
    protected function context(): array
    {
        $resource = request()->route('resource'); [$model, $title] = Resources::definition($resource);
        return compact('resource', 'model', 'title');
    }
    public function index(Request $request)
    {
        extract($this->context()); Gate::authorize('viewAny', $model);
        $query = $model::query(); $column = in_array($resource, ['property-types', 'locations', 'amenities', 'portfolio-categories', 'testimonials', 'team']) ? 'name' : 'title';
        if ($search = $request->string('q')->limit(100)->toString()) { $query->where($column, 'like', '%'.$search.'%'); }
        return view('admin.index', compact('resource', 'title', 'column') + ['records' => $query->latest()->paginate(15)->withQueryString()]);
    }
    public function create()
    {
        extract($this->context()); Gate::authorize('create', $model);
        return view('admin.form', compact('resource', 'title') + ['record' => new $model, 'fields' => Resources::fields($resource)]);
    }
    public function edit(int $record)
    {
        extract($this->context()); $record = $model::findOrFail($record); Gate::authorize('update', $record);
        if ($resource === 'portfolio') { $record->load('images'); }
        return view('admin.form', compact('resource', 'title', 'record') + ['fields' => Resources::fields($resource)]);
    }
    public function store(ContentRequest $request, MediaService $media) { return $this->save($request, $media); }
    public function update(ContentRequest $request, MediaService $media, int $record) { return $this->save($request, $media, $record); }
    protected function save(ContentRequest $request, MediaService $media, ?int $id = null)
    {
        extract($this->context()); $record = $id ? $model::findOrFail($id) : new $model; Gate::authorize($id ? 'update' : 'create', $id ? $record : $model);
        $data = $request->validated();
        DB::transaction(function () use ($data, $record, $resource, $media, $id) {
            $attributes = collect($data)->except(['gallery', 'images', 'remove_images', 'translations'])->all();
            foreach (Resources::fields($resource) as $field => $definition) {
                if ($definition['type'] === 'image') { if (! empty($attributes[$field])) { $attributes[$field] = $media->store($attributes[$field]); } else { unset($attributes[$field]); } }
            }
            $record->fill($attributes)->save();
            if (array_key_exists('translations',$data)) $record->saveTranslation($data['translations'] ?? []);
            if ($resource === 'portfolio') { $media->syncGallery($record, $data); }
            Audit::record($id ? 'updated' : 'created', $record);
        });
        return redirect()->route('admin.'.$resource.'.edit', $record)->with('success', 'Changes saved successfully.');
    }
    public function destroy(int $record)
    {
        extract($this->context()); $record = $model::findOrFail($record); Gate::authorize('delete', $record);
        $files = collect([$record->featured_image, $record->photo])->filter();
        if ($resource === 'portfolio') { $files = $files->merge($record->images()->pluck('image_path')); }
        try { DB::transaction(function () use ($record) { Audit::record('deleted', $record); $record->delete(); }); }
        catch (\Illuminate\Database\QueryException $e) { if (in_array($e->getCode(), ['23000', 23000])) { return back()->with('error', 'This item is in use. Reassign its related records before deleting it.'); } throw $e; }
        Storage::disk('public')->delete($files->all());
        return redirect()->route('admin.'.$resource.'.index')->with('success', 'Item deleted.');
    }
}
