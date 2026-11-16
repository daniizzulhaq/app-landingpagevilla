<?php
namespace App\Http\Controllers;
use App\Models\{Portfolio, PortfolioCategory};
use Illuminate\Http\Request;
class PortfolioController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['category' => ['nullable', 'integer', 'exists:portfolio_categories,id']]);
        $projects = Portfolio::published()->with('category')->when($data['category'] ?? null, fn ($q, $id) => $q->where('category_id', $id))->latest()->paginate(9)->withQueryString();
        return view('public.portfolio', ['projects' => $projects, 'categories' => PortfolioCategory::orderBy('name')->get()]);
    }
    public function show(string $slug) { return view('public.project', ['project' => Portfolio::published()->with(['category', 'images'])->where('slug', $slug)->firstOrFail()]); }
}
