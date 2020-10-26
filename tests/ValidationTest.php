<?php
declare(strict_types=1);

namespace Livy\Plumbing\NormalizeLinks;

use PHPUnit\Framework\TestCase;

require __DIR__.'/mock.php';

final class ValidationTest extends TestCase
{
    public function testRecognizesAUrl(): void
    {
        $this->assertTrue(Validation::url('https://www.alwaysblank.org'));
        $this->assertFalse(Validation::url('alwaysblank.org'));
        $this->assertTrue(Validation::url('/alwaysblank'));
        $this->assertFalse(Validation::url(''));
    }

    public function testRecognizesATitle(): void
    {
        $this->assertTrue(Validation::title('This is a Title'));
        $this->assertFalse(Validation::title(''));
    }

    public function testCanDetermineExternalLink(): void
    {
        $this->assertTrue(Validation::probablyExternal('https://google.com'));
        $this->assertTrue(Validation::probablyExternal('https://google.com', 'https://altavista.com'));
        $this->assertFalse(Validation::probablyExternal('https://www.alwaysblank.org'));
        $this->assertFalse(Validation::probablyExternal('https://www.alwaysblank.org/page/'));
        $this->assertFalse(Validation::probablyExternal('https://www.alwaysblank.org/'));
        $this->assertFalse(Validation::probablyExternal('/alwaysblank'));
    }
}

