<?php
namespace Tests\Unit;
use App\Services\WhatsAppService;
use PHPUnit\Framework\TestCase;
class WhatsAppTest extends TestCase
{
    public function test_international_number_normalization(): void
    {
        foreach(['+62 812-3456-7890','081234567890','006281234567890','6281234567890'] as $input) $this->assertSame('6281234567890',WhatsAppService::normalize($input));
        foreach([null,'','123','0000000000','1234567890123456'] as $input) $this->assertNull(WhatsAppService::normalize($input));
        $this->assertSame('447911123456',WhatsAppService::normalize('+44 7911 123456'));
    }
}
