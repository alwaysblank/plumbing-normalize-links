<?php
declare(strict_types=1);

namespace Livy\Plumbing\NormalizeLinks;

use PHPUnit\Framework\TestCase;

require __DIR__.'/mock.php';

final class NormalizedTest extends TestCase
{
    protected $normalizedValid;
    protected $normalizedInvalid;

    protected function setUp(): void
    {
        $this->normalizedValid = new Normalized([
            'url'    => 'https://www.alwaysblank.org',
            'title'  => 'Always Blank',
            'target' => '_blank',
        ]);

        $this->normalizedInvalid = new Normalized([
            'url' => false,
        ]);
    }

    public function testInstantiationWorksCorrectly(): void
    {
        $this->assertTrue(is_a($this->normalizedValid, Normalized::class));
        $this->assertTrue(is_a($this->normalizedInvalid, Normalized::class));
    }

    public function testGetUrl(): void
    {
        $this->assertSame('https://www.alwaysblank.org', $this->normalizedValid->url());
        $this->assertNull($this->normalizedInvalid->url());
    }

    public function testGetLabel(): void
    {
        $this->assertSame('Always Blank', $this->normalizedValid->label());
        $this->assertNull($this->normalizedInvalid->label());
    }

    public function testGetNewTab(): void
    {
        // This is because we've manually set target = _blank
        $this->assertTrue($this->normalizedValid->newTab());
        $this->assertNull($this->normalizedInvalid->newTab());
    }

    public function testProbablyExternal(): void
    {
        $this->assertFalse($this->normalizedValid->probablyExternal());
        $this->assertNull($this->normalizedInvalid->probablyExternal());
        $Normalized = new Normalized('https://www.google.com');
        $this->assertTrue($Normalized->probablyExternal());
    }

    public function testSetValue(): void
    {
        $this->normalizedValid->set('label', "New Label");
        $this->assertSame("New Label", $this->normalizedValid->label());
    }

    public function testReevaluatedUrlWhenSetDynamically(): void
    {
        $this->normalizedValid->set('url', 'https://www.google.com');
        $this->assertTrue($this->normalizedValid->probablyExternal());
    }

    public function testHandleBadLinkArgument(): void
    {
        $Normalized = new Normalized(44);
        $this->assertFalse($Normalized->valid());
    }

    public function testCompleteValidatePart(): void
    {
        $source = ['nothing' => 'nobody'];
        $this->assertFalse($this->normalizedValid->validatePart('nothing', $source));
    }

    public function testHandleInvalidSettings(): void
    {
        $Normalized = new Normalized('https://www.alwaysblank.org', 44);
        $this->assertTrue($Normalized->valid());
    }

    public function testSetDefaultLabel(): void
    {
        $Normalized = new Normalized('https://www.alwaysblank.org', ['label' => "Come Say Hi"]);
        $this->assertSame("Come Say Hi", $Normalized->label());
    }

    public function testSetExternalNewTa(): void
    {
        $Normalized = new Normalized('https://www.google.org', ['external_in_new_tab' => false]);
        $this->assertFalse($Normalized->newTab());
    }
}
