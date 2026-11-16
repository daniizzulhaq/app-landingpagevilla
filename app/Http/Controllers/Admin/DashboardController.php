<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{Property, Portfolio, Testimonial, AuditLog};
class DashboardController extends Controller
{
    public function __invoke()
    {
        $counts = Property::query()->selectRaw("COUNT(*) AS total, SUM(CASE WHEN status = 'available' AND publication_status = 'published' THEN 1 ELSE 0 END) AS active, SUM(CASE WHEN listing_type = 'sale' THEN 1 ELSE 0 END) AS sale, SUM(CASE WHEN listing_type = 'rent' THEN 1 ELSE 0 END) AS rent, SUM(CASE WHEN status IN ('sold', 'rented') THEN 1 ELSE 0 END) AS completed")->first();
        return view('admin.dashboard', ['counts' => $counts, 'portfolioCount' => Portfolio::count(), 'testimonialCount' => Testimonial::where('is_active', true)->count(), 'activities' => AuditLog::with('user')->latest()->limit(8)->get()]);
    }
}
