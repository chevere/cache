<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevere.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Tests;

use Chevere\Cache\Cache;
use Chevere\Cache\Interfaces\ItemInterface;
use Chevere\Filesystem\Interfaces\DirectoryInterface;
use Chevere\Tests\src\DirectoryHelper;
use Chevere\VarSupport\StorableVariable;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

final class CacheTest extends TestCase
{
    private DirectoryInterface $resourcesDirectory;

    protected function setUp(): void
    {
        $this->resourcesDirectory = (new DirectoryHelper($this))->directory();
        $this->resourcesDirectory->createIfNotExists();
    }

    protected function tearDown(): void
    {
        $this->resourcesDirectory->removeIfExists();
    }

    public function testConstruct(): void
    {
        $cache = new Cache($this->resourcesDirectory);
        $this->assertSame($cache->directory(), $this->resourcesDirectory);
    }

    public function testConstructDirNotExists(): void
    {
        $dir = $this->resourcesDirectory->getChild('delete/');
        $dirPath = $dir->path()->__toString();
        $this->assertDirectoryDoesNotExist($dir->path()->__toString());
        new Cache($dir);
        $this->assertDirectoryExists($dirPath);
        $dir->remove();
    }

    public function testKeyNotExists(): void
    {
        $cache = new Cache($this->resourcesDirectory);
        $key = uniqid();
        $this->assertFalse($cache->exists($key));
    }

    public function testGetNotExists(): void
    {
        $key = uniqid();
        $this->expectException(OutOfBoundsException::class);
        (new Cache($this->resourcesDirectory))->get($key);
    }

    public function testWithPutWithRemove(): void
    {
        $key = uniqid();
        $var = [
            time(),
            false,
            'test',
            $this->resourcesDirectory->getChild('test/'),
            13.13,
        ];
        $storable = new StorableVariable($var);
        $cache = new Cache($this->resourcesDirectory);
        $cacheWithPut = $cache->withPut(...[
            $key => $storable,
        ]);
        $this->assertEquals($var, $cacheWithPut->get($key)->get());
        $this->assertNotSame($cache, $cacheWithPut);
        $this->assertArrayHasKey($key, $cacheWithPut->puts());
        $this->assertArrayHasKey(
            'path',
            $cacheWithPut->puts()[$key]
        );
        $this->assertArrayHasKey(
            'checksum',
            $cacheWithPut->puts()[$key]
        );
        $this->assertTrue($cacheWithPut->exists($key));
        $this->assertInstanceOf(
            ItemInterface::class,
            $cacheWithPut->get($key)
        );
        $cacheWithout = $cacheWithPut->withRemove($key);
        $this->assertNotSame($cacheWithPut, $cacheWithout);
        $this->assertArrayNotHasKey($key, $cacheWithout->puts());
        $this->assertFalse($cacheWithout->exists($key));
    }
}
