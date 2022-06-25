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
use Chevere\Cache\Key;
use Chevere\Filesystem\Interfaces\DirInterface;
use Chevere\Tests\src\DirHelper;
use Chevere\Throwable\Exceptions\OutOfBoundsException;
use Chevere\VarSupport\VarStorable;
use PHPUnit\Framework\TestCase;

final class CacheTest extends TestCase
{
    private DirInterface $resourcesDir;

    protected function setUp(): void
    {
        $this->resourcesDir = (new DirHelper($this))->dir();
        $this->resourcesDir->createIfNotExists();
    }

    public function tearDown(): void
    {
        $this->resourcesDir->removeIfExists();
    }

    public function testConstruct(): void
    {
        $cache = new Cache($this->resourcesDir);
        $this->assertSame($cache->dir(), $this->resourcesDir);
    }

    public function testConstructDirNotExists(): void
    {
        $dir = $this->resourcesDir->getChild('delete/');
        $dirPath = $dir->path()->__toString();
        $this->assertDirectoryDoesNotExist($dir->path()->__toString());
        new Cache($dir);
        $this->assertDirectoryExists($dirPath);
        $dir->remove();
    }

    public function testKeyNotExists(): void
    {
        $cache = new Cache($this->resourcesDir);
        $key = new Key(uniqid());
        $this->assertFalse($cache->exists($key));
    }

    public function testGetNotExists(): void
    {
        $key = new Key(uniqid());
        $this->expectException(OutOfBoundsException::class);
        (new Cache($this->resourcesDir))->get($key);
    }

    public function testWithPutWithRemove(): void
    {
        $uniqid = uniqid();
        $var = [
            time(),
            false,
            'test',
            $this->resourcesDir->getChild('test/'),
            13.13
        ];
        $varStorable = new VarStorable($var);
        $key = new Key($uniqid);
        $cache = new Cache($this->resourcesDir);
        $cacheWithPut = $cache->withPut($key, $varStorable);
        $this->assertNotSame($cache, $cacheWithPut);
        $this->assertArrayHasKey($uniqid, $cacheWithPut->puts());
        $this->assertArrayHasKey(
            'path',
            $cacheWithPut->puts()[$uniqid]
        );
        $this->assertArrayHasKey(
            'checksum',
            $cacheWithPut->puts()[$uniqid]
        );
        $this->assertTrue($cacheWithPut->exists($key));
        $this->assertInstanceOf(
            ItemInterface::class,
            $cacheWithPut->get($key)
        );
        $cacheWithout = $cacheWithPut->without($key);
        $this->assertNotSame($cacheWithPut, $cacheWithout);
        $this->assertArrayNotHasKey($uniqid, $cacheWithout->puts());
        $this->assertFalse($cacheWithout->exists($key));
    }
}
