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
use Chevere\VarSupport\StorableVariable;
use PHPUnit\Framework\TestCase;
use stdClass;
use TypeError;

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

    public function testGetNotExists(): void
    {
        $file = $this->getDisposablePhpFileReturn('test');
        $item = $this->getCacheItem($file->path());
        $file->remove();
        $this->expectException(FileNotExistsException::class);
        $item->get();
    }

    /**
     * @dataProvider getProvider
     */
    public function testGetType(mixed $value, string $expected, string $fail): void
    {
        $file = $this->getDisposablePhpFileReturn($value);
        $item = $this->getCacheItem($file->path());
        $assertMethod = 'assert';
        $assertMethod .= $expected === 'object'
            ? 'Equals'
            : 'Same';
        $this->{$assertMethod}($value, $item->cast()->{$expected}());
        $this->expectException(TypeError::class);
        $item->cast()->{$fail}();
    }

    public function getProvider(): array
    {
        return [
            [['test'], 'array', 'bool'],
            [true, 'bool', 'int'],
            [1.1, 'float', 'string'],
            [1, 'int', 'array'],
            ['test', 'string', 'float'],
            [new stdClass(), 'object', 'array'],
        ];
    }

    private function getDisposablePhpFileReturn(mixed $variable): FileInterface
    {
        $path = $this->directory->path()->getChild('return.php');
        $file = new File($path);
        $filePhp = new FilePhp($file);
        $filePhpReturn = new FilePhpReturn($filePhp);
        $file->create();
        $filePhpReturn->put(new StorableVariable($variable));

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
}
