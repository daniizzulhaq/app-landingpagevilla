<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\SettingRequest;
use App\Models\WebsiteSetting;
use App\Services\{Audit, Settings, MediaService, WhatsAppService};
use App\Support\SettingFields;
use Illuminate\Support\Facades\{DB, Gate};
class WebsiteSettingController extends Controller
{
    public function edit(string $group = 'company')
    {
        Gate::authorize('viewAny', WebsiteSetting::class); $fields = SettingFields::groups()[$group] ?? abort(404);
        return view('admin.settings', compact('group', 'fields') + ['values' => WebsiteSetting::pluck('value', 'key')->all()]);
    }
    public function update(SettingRequest $request, MediaService $media, Settings $settings, string $group = 'company')
    {
        Gate::authorize('create', WebsiteSetting::class); $data = $request->validated();
        DB::transaction(function () use ($group, $data, $media) {
            foreach (SettingFields::groups()[$group] ?? abort(404) as $key => [$label, $type]) {
                if (!array_key_exists(str_replace('.', '__', $key),$data)) continue;
                $value = $data[str_replace('.', '__', $key)] ?? null;
                if ($type === 'image') { if (! $value) { continue; } $value = $media->store($value); }
                if ($key === 'company.whatsapp' && $value) { $value = WhatsAppService::normalize($value); }
                $setting = WebsiteSetting::updateOrCreate(['key' => $key], ['value' => $value, 'type' => $type]); Audit::record('updated', $setting);
            }
        });
        $settings->forget(); return back()->with('success', 'Website settings saved.');
    }
}
