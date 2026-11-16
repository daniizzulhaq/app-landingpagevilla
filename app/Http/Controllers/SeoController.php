<?php
namespace App\Http\Controllers;
use App\Models\{Page, Property, Portfolio, Service};
class SeoController extends Controller
{
    public function sitemap()
    {
        $urls = collect([url('/'), route('services.index'), route('properties.index'), route('portfolio.index'), route('contact'), route('buy-rent'),route('company.profile')])->map(fn ($url) => ['url' => $url, 'modified' => null]);
        foreach ([Property::class => 'properties.show', Portfolio::class => 'portfolio.show'] as $model => $route) {
            foreach ($model::published()->where('is_demo', false)->select(['slug', 'updated_at'])->cursor() as $record) { $urls->push(['url' => route($route, $record->slug), 'modified' => $record->updated_at->toAtomString()]); }
        }
        foreach (Service::where('is_active', true)->select(['slug', 'updated_at'])->cursor() as $service) { $urls->push(['url' => route('services.show', $service->slug), 'modified' => $service->updated_at->toAtomString()]); }
        foreach (Page::where('publication_status', 'published')->where('is_indexable', true)->select(['slug', 'updated_at'])->cursor() as $page) { $urls->push(['url' => url('/'.$page->slug), 'modified' => $page->updated_at->toAtomString()]); }
        $urls=$urls->flatMap(fn($item)=>[$item,['url'=>$item['url'].'?lang=id','modified'=>$item['modified']]]);
        return response()->view('seo.sitemap', compact('urls'))->header('Content-Type', 'application/xml; charset=UTF-8');
    }
    public function robots() { return response("User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /property-selection\nDisallow: /favorites\nDisallow: /compare\nSitemap: ".url('/sitemap.xml')."\n", 200)->header('Content-Type', 'text/plain'); }
}
