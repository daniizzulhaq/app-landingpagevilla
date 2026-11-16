<?php
namespace App\Http\Controllers;
use App\Models\Page;
class PageController extends Controller
{
    public function show(string $slug) { return view('public.page', ['page' => Page::where('slug', $slug)->where('publication_status', 'published')->firstOrFail()]); }
}
