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

use Chevere\Cache\Interfaces\ItemInterface;
use Chevere\Cache\Item;
use Chevere\Filesystem\Exceptions\FileNotExistsException;
use Chevere\Filesystem\File;
use Chevere\Filesystem\FilePhp;
use Chevere\Filesystem\FilePhpReturn;
use Chevere\Filesystem\Interfaces\DirInterface;
use Chevere\Filesystem\Interfaces\FileInterface;
use Chevere\Filesystem\Interfaces\PathInterface;
use Chevere\Tests\src\DirHelper;
use Chevere\VarSupport\VarStorable;
use PHPUnit\Framework\TestCase;
use function Safe\file_put_contents;

final class ItemTest extends TestCase
{
    private DirInterface $dir;

    public function setUp(): void
    {
        $this->dir = (new DirHelper($this))->dir();
        $this->dir->createIfNotExists();
    }

    public function tearDown(): void
    {
        $this->dir->removeIfExists();
    }

    public function testVarThrowsException(): void
    {
        $file = $this->getDisposablePhpFileReturn();
        $item = $this->getCacheItem($file->path());
        $file->remove();
        $this->expectException(FileNotExistsException::class);
        $item->var();
    }

    public function testRawThrowsException(): void
    {
        $file = $this->getDisposablePhpFileReturn();
        $item = $this->getCacheItem($file->path());
        $file->remove();
        $this->expectException(FileNotExistsException::class);
        $item->raw();
    }

    public function testNotSerialized(): void
    {
        $path = $this->dir->path()->getChild('return.php');
        $this->getDisposablePhpFileReturn();
        $item = $this->getCacheItem($path);
        $var = include $path->__toString();
        $this->assertSame($var, $item->raw());
        $this->assertSame($var, $item->var());
    }

    public function testSerialized(): void
    {
        $path = $this->dir->path()->getChild('return-serialized.php');
        $this->writeSerialized($path);
        $item = $this->getCacheItem($path);
        $var = include $path->__toString();
        $this->assertSame($var, $item->raw());
        $this->assertEqualsCanonicalizing(
            unserialize($var),
            $item->var()
        );
        unlink($path->__toString());
    }

    private function getDisposablePhpFileReturn(): FileInterface
    {
        $path = $this->dir->path()->getChild('return.php');
        $file = new File($path);
        $file->create();
        $file->put("<?php return '';");

        return $file;
    }

    private function getCacheItem(PathInterface $path): ItemInterface
    {
        return new Item(
            new FilePhpReturn(
                new FilePhp(
                    new File($path)
                )
            )
        );
    }

    private function writeSerialized(PathInterface $path): void
    {
        if (!$path->exists()) {
            file_put_contents($path->__toString(), '');
        }
        $fileReturn = new FilePhpReturn(
            new FilePhp(
                new File($path)
            )
        );
        $fileReturn->put(
            new VarStorable($path)
        );
    }
}
