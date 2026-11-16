<?php
namespace App\Http\Controllers;
use App\Models\Page;
class AboutController extends Controller
{
    public function __invoke() { return view('public.about', [
        'page'=>Page::where('slug','about-us')->where('publication_status','published')->firstOrFail(),
        'services'=>\App\Models\Service::where('is_active',true)->orderBy('sort_order')->get(),
        'team'=>\App\Models\TeamMember::where('is_active',true)->orderBy('sort_order')->get(),
    ]); }
}
