<?php
namespace Tests;
use Illuminate\Foundation\Testing\DatabaseTransactions;
abstract class PropertymeTestCase extends TestCase
{
    use DatabaseTransactions;
    public function createApplication()
    {
        $app = parent::createApplication();
        if (! str_ends_with((string) $app['config']->get('database.connections.mysql.database'), '_test')) { throw new \RuntimeException('Tests require a separate database whose name ends in _test.'); }
        return $app;
    }
    protected function setUp(): void { parent::setUp(); $this->seed(); }
}
