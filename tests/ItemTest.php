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
use Chevere\Filesystem\Interfaces\DirectoryInterface;
use Chevere\Filesystem\Interfaces\FileInterface;
use Chevere\Filesystem\Interfaces\PathInterface;
use Chevere\Tests\src\DirectoryHelper;
use Chevere\VariableSupport\StorableVariable;
use PHPUnit\Framework\TestCase;
use function Safe\file_put_contents;

final class ItemTest extends TestCase
{
    private DirectoryInterface $directory;

    protected function setUp(): void
    {
        $this->directory = (new DirectoryHelper($this))->directory();
        $this->directory->createIfNotExists();
    }

    protected function tearDown(): void
    {
        $this->directory->removeIfExists();
    }

    public function testVarThrowsException(): void
    {
        $file = $this->getDisposablePhpFileReturn();
        $item = $this->getCacheItem($file->path());
        $file->remove();
        $this->expectException(FileNotExistsException::class);
        $item->variable();
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
        $path = $this->directory->path()->getChild('return.php');
        $this->getDisposablePhpFileReturn();
        $item = $this->getCacheItem($path);
        $var = include $path->__toString();
        $this->assertSame($var, $item->raw());
        $this->assertSame($var, $item->variable());
    }

    public function testSerialized(): void
    {
        $path = $this->directory->path()->getChild('return-serialized.php');
        $this->writeSerialized($path);
        $item = $this->getCacheItem($path);
        $var = include $path->__toString();
        $this->assertSame($var, $item->raw());
        $this->assertEqualsCanonicalizing(
            unserialize($var),
            $item->variable()
        );
        unlink($path->__toString());
    }

    private function getDisposablePhpFileReturn(): FileInterface
    {
        $path = $this->directory->path()->getChild('return.php');
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
        if (! $path->exists()) {
            file_put_contents($path->__toString(), '');
        }
        $fileReturn = new FilePhpReturn(
            new FilePhp(
                new File($path)
            )
        );
        $fileReturn->put(
            new StorableVariable($path)
        );
    }
}
