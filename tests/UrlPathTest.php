<?php

namespace Test;

use Test\Cases\Dummy\DummyUrlStrategy;
use Vinograd\Path\DefaultUrlPathStrategy;
use Vinograd\Path\Exception\InvalidPathException;
use Vinograd\Path\UrlStrategy;
use Vinograd\Path\UrlPath;
use PHPUnit\Framework\TestCase;

class UrlPathTest extends TestCase
{

    public function testConstruct()
    {
        $urlPath = new UrlPath($path = 'path/to/resource');
        self::assertEquals($path, $urlPath->getSource());
        self::assertInstanceOf(DefaultUrlPathStrategy::class, $urlPath->getStrategy());

        $urlPath = new UrlPath($path, $strategy = new DummyUrlStrategy());
        self::assertSame($strategy, $urlPath->getStrategy());
    }

    public function testConstructBad()
    {
        $this->expectException(InvalidPathException::class);
        new UrlPath('');
    }

    public function testGetSeparator()
    {
        $updateStrategy = $this->getMockForAbstractClass(UrlStrategy::class);
        $urlPath = new UrlPath('path', $updateStrategy);
        self::assertEquals('/', $urlPath->getSeparator());
    }

    public function testSetUpdateStrategy()
    {
        $updateStrategy = $this->getMockForAbstractClass(UrlStrategy::class);
        $updateStrategy2 = $this->getMockForAbstractClass(UrlStrategy::class);
        $urlPath = new UrlPath('path', $updateStrategy);
        $reflection = new \ReflectionObject($urlPath);
        $property = $reflection->getProperty('strategy');
        $property->setAccessible(true);
        $objectValue = $property->getValue($urlPath);
        $urlPath->setStrategy($updateStrategy2);

        $property = $reflection->getProperty('strategy');
        $property->setAccessible(true);
        $objectValue2 = $property->getValue($urlPath);
        self::assertNotSame($objectValue, $objectValue2);
    }

    public function testEqualsStrategy()
    {
        $updateStrategy = $this->getMockForAbstractClass(UrlStrategy::class);
        $updateStrategy2 = $this->getMockForAbstractClass(UrlStrategy::class);
        $urlPath = new UrlPath('path', $updateStrategy);
        self::assertFalse($urlPath->equalsStrategy($updateStrategy2));
        self::assertTrue($urlPath->equalsStrategy($updateStrategy));
    }

    public function testEqualsSuffix()
    {
        $urlPath = new UrlPath('path');
        $urlPath->setSuffix('suff1');
        self::assertFalse($urlPath->equalsSuffix('suff'));
        self::assertTrue($urlPath->equalsSuffix('suff1'));
        $urlPath->setSuffix('suff1');
        self::assertTrue($urlPath->equalsSuffix('suff1'));
        $urlPath->setSuffix('suff');
        self::assertTrue($urlPath->equalsSuffix('suff'));
    }

    public function testGetSuffix()
    {
        $urlPath = new UrlPath('path');
        self::assertEmpty($urlPath->getSuffix());
        $urlPath->setSuffix('suff');
        self::assertEquals('suff', $urlPath->getSuffix());
        self::assertEquals('suff', $urlPath->getSuffix());
    }

    public function testSetSuffix()
    {
        $urlPath = new UrlPath('path');
        $urlPath->setSuffix(null);
        self::assertEmpty($urlPath->getSuffix());
        $urlPath->setSuffix('suff');
        self::assertEquals('suff', $urlPath->getSuffix());
        $urlPath->setSuffix(null);
        self::assertEmpty($urlPath->getSuffix());
    }

    public function testUpdateSource()
    {
        $urlPath = new UrlPath('assert/update/');
        self::assertEquals('assert/update', (string)$urlPath);
        $urlPath->updateSource();
        self::assertEquals('assert/update', (string)$urlPath);
    }

    public function testReset()
    {
        $urlPath = new UrlPath('assert/update/');
        $urlPath->setSuffix('suff');

        $urlPath->reset();

        self::assertEmpty($urlPath->getSource());
        self::assertEmpty($urlPath->getAll());
        self::assertEmpty($urlPath->getSuffix());
    }

    public function testSetSource()
    {
        $urlPath = new UrlPath('assert/update/');
        self::assertEquals('assert/update', (string)$urlPath);
        $urlPath->setSource('assert2/update2/');
        self::assertEquals('assert2/update2', (string)$urlPath);
        $urlPath->setSource('assert2/update2');
        self::assertEquals('assert2/update2', (string)$urlPath);
        $urlPath->setSource('/assert2/update2');
        self::assertEquals('/assert2/update2', (string)$urlPath);
    }

    public function testSetSourceEmpty()
    {
        $urlPath = new UrlPath('assert/update/');
        self::assertEquals('assert/update', (string)$urlPath);
        $urlPath->setSource('');
        self::assertEmpty((string)$urlPath);
        self::assertEmpty($urlPath->getAll());
    }

    public function testConstructEmpty()
    {
        $this->expectException(InvalidPathException::class);
        new UrlPath('');
    }

    public function testCreateBlank()
    {
        $urlPath = UrlPath::createBlank();
        $urlPath2 = UrlPath::createBlank();
        self::assertNotSame($urlPath, $urlPath2);
        self::assertInstanceOf(DefaultUrlPathStrategy::class, $urlPath->getStrategy());
        self::assertEmpty($urlPath->getSource());
        self::assertEmpty($urlPath->getAll());
        self::assertEmpty($urlPath->getSuffix());

        $urlPath2 = UrlPath::createBlank($updateStrategy = new DummyUrlStrategy());
        self::assertSame($updateStrategy, $urlPath2->getStrategy());
        self::assertEmpty($urlPath2->getSource());
        self::assertEmpty($urlPath2->getAll());
        self::assertEmpty($urlPath2->getSuffix());
    }
}
