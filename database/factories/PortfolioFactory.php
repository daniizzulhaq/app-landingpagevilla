<?php
namespace Database\Factories;
use App\Models\{Portfolio, PortfolioCategory};
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
class PortfolioFactory extends Factory
{
    protected $model = Portfolio::class;
    public function definition(): array { return ['title'=>'Test Project '.Str::random(6),'slug'=>'test-project-'.Str::lower(Str::random(10)), 'category_id'=>PortfolioCategory::firstOrCreate(['slug'=>'villa-development'],['name'=>'Villa Development'])->id, 'description'=>'Isolated test project.', 'publication_status'=>'published','is_featured'=>false]; }
}
