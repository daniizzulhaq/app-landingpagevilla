<?php

namespace App\Http\Controllers;

use App\Models\{Page, Portfolio, Service, TeamMember};

class CompanyProfileController extends Controller
{
    public function __invoke()
    {
        return view('public.company-profile',[
            'page'=>Page::where('slug','about-us')->where('publication_status','published')->first(),
            'services'=>Service::where('is_active',true)->orderBy('sort_order')->get(),
            'projects'=>Portfolio::published()->where('is_demo',false)->with('category')->latest()->limit(4)->get(),
            'team'=>TeamMember::where('is_active',true)->orderBy('sort_order')->get(),
        ]);
    }
}
