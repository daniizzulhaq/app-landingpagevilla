<?php
namespace App\Http\Controllers;
use App\Models\Service;
class ServiceController extends Controller
{
    public function index() { return view('public.services', ['services' => Service::where('is_active', true)->orderBy('sort_order')->get()]); }
    public function show(string $slug) { return view('public.service', ['service' => Service::where('is_active', true)->where('slug', $slug)->firstOrFail()]); }
}
