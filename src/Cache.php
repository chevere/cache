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

namespace Chevere\Cache;

use Chevere\Cache\Interfaces\CacheInterface;
use Chevere\Cache\Interfaces\ItemInterface;
use Chevere\Cache\Interfaces\KeyInterface;
use Chevere\Filesystem\Exceptions\DirectoryUnableToCreateException;
use Chevere\Filesystem\File;
use Chevere\Filesystem\FilePhp;
use Chevere\Filesystem\FilePhpReturn;
use Chevere\Filesystem\Interfaces\DirectoryInterface;
use Chevere\Filesystem\Interfaces\PathInterface;
use function Chevere\Message\message;
use Chevere\Throwable\Exceptions\OutOfBoundsException;
use Chevere\Throwable\Exceptions\RuntimeException;
use Chevere\VariableSupport\Interfaces\StorableVariableInterface;
use Throwable;

final class Cache implements CacheInterface
{
    /**
     * [key => [checksum => , path =>]]
     *
     * @var array<string, array<string, string>>
     */
    private array $puts;

    /**
     * @throws DirectoryUnableToCreateException
     */
    public function __construct(
        private DirectoryInterface $directory
    ) {
        if (!$this->directory->exists()) {
            $this->directory->create();
        }
        $this->puts = [];
    }

    public function directory(): DirectoryInterface
    {
        return $this->directory;
    }

    public function withPut(KeyInterface $key, StorableVariableInterface $variable): CacheInterface
    {
        $path = $this->getPath($key->__toString());

        try {
            $file = new File($path);
            if (!$file->exists()) {
                $file->create();
            }
            $filePhp = new FilePhp($file);
            $fileReturn = new FilePhpReturn($filePhp);
            $fileReturn->put($variable);
            // @infection-ignore-all
            try {
                $filePhp->compileCache();
            }
            // @codeCoverageIgnoreStart
            catch (Throwable) {
                // Don't panic if OPCache fails
            }
            // @codeCoverageIgnoreEnd
            $new = clone $this;
            $new->puts[$key->__toString()] = [
                'path' => $fileReturn->filePhp()->file()->path()->__toString(),
                'checksum' => $fileReturn->filePhp()->file()->getChecksum(),
            ];
        }
        // @codeCoverageIgnoreStart
        // @infection-ignore-all
        catch (Throwable $e) {
            throw new RuntimeException(previous: $e);
        }
        // @codeCoverageIgnoreEnd

        return $new;
    }

    /**
     * @infection-ignore-all
     */
    public function without(KeyInterface $cacheKey): CacheInterface
    {
        $new = clone $this;
        $path = $this->getPath($cacheKey->__toString());

        try {
            if (!$path->exists()) {
                // @codeCoverageIgnoreStart
                return $new;
                // @codeCoverageIgnoreEnd
            }
            $filePhp = new FilePhp(new File($path));
            // @infection-ignore-all
            $filePhp->flushCache();
            $filePhp->file()->remove();
        }
        // @codeCoverageIgnoreStart
        // @infection-ignore-all
        catch (Throwable $e) {
            throw new RuntimeException(previous: $e);
        }
        // @codeCoverageIgnoreEnd
        unset($new->puts[$cacheKey->__toString()]);

        return $new;
    }

    public function exists(KeyInterface $cacheKey): bool
    {
        return $this->getPath($cacheKey->__toString())->exists();
    }

    public function get(KeyInterface $cacheKey): ItemInterface
    {
        $path = $this->getPath($cacheKey->__toString());
        if (!$path->exists()) {
            throw new OutOfBoundsException(
                message('No cache for key %key%')
                    ->withCode('%key%', $cacheKey->__toString())
            );
        }

        return new Item(
            new FilePhpReturn(
                new FilePhp(
                    new File($path)
                )
            )
        );
    }

    public function puts(): array
    {
        return $this->puts;
    }

    private function getPath(string $name): PathInterface
    {
        $child = $name . '.php';

        return $this->directory->path()->getChild($child);
    }
}
