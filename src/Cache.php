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
use Chevere\Filesystem\Exceptions\DirectoryUnableToCreateException;
use Chevere\Filesystem\File;
use Chevere\Filesystem\FilePhp;
use Chevere\Filesystem\FilePhpReturn;
use Chevere\Filesystem\Interfaces\DirectoryInterface;
use Chevere\Filesystem\Interfaces\FilePhpReturnInterface;
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
        if (! $this->directory->exists()) {
            $this->directory->create();
        }
        $this->puts = [];
    }

    public function directory(): DirectoryInterface
    {
        return $this->directory;
    }

    public function withPut(StorableVariableInterface ...$storable): CacheInterface
    {
        $new = clone $this;
        foreach ($storable as $key => $variable) {
            $key = strval(new Key(strval($key)));
            $filePhpReturn = $this->getFilePhpReturn($key);
            $filePhp = $filePhpReturn->filePhp();
            $file = $filePhp->file();

            if (! $file->exists()) {
                $file->create();
            }
            $filePhpReturn->put($variable);
            // @infection-ignore-all
            try {
                $filePhp->compileCache();
            } catch (Throwable) { // @codeCoverageIgnoreStart
                // Don't panic if OPCache fails
            }
            // @codeCoverageIgnoreEnd
            $new->puts[$key] = [
                'path' => $file->path()->__toString(),
                'checksum' => $file->getChecksum(),
            ];
            // @codeCoverageIgnoreEnd
        }

        return $new;
    }

    public function withRemove(string ...$key): CacheInterface
    {
        $new = clone $this;
        foreach ($key as $item) {
            $itemKey = strval(new Key($item));
            $filePhpReturn = $this->getFilePhpReturn($itemKey);
            $filePhp = $filePhpReturn->filePhp();
            $file = $filePhp->file();

            try {
                if (! $file->exists()) {
                    // @codeCoverageIgnoreStart
                    return $new;
                    // @codeCoverageIgnoreEnd
                }
                // @infection-ignore-all
                $filePhp->flushCache();
                $file->remove();
            } catch (Throwable $e) { // @codeCoverageIgnoreStart
                throw new RuntimeException(previous: $e);
            }
            // @codeCoverageIgnoreEnd
            unset($new->puts[$itemKey]);
        }

        return $new;
    }

    public function exists(string ...$key): bool
    {
        foreach ($key as $item) {
            $filePhpReturn = $this->getFilePhpReturn(strval(new Key($item)));
            $file = $filePhpReturn->filePhp()->file();
            if (! $file->exists()) {
                return false;
            }
        }

        return true;
    }

    public function get(string $key): ItemInterface
    {
        $filePhpReturn = $this->getFilePhpReturn($key);
        $file = $filePhpReturn->filePhp()->file();
        if (! $file->exists()) {
            throw new OutOfBoundsException(
                message('No cache for key %key%')
                    ->withCode('%key%', $key)
            );
        }

        return new Item($filePhpReturn);
    }

    public function puts(): array
    {
        return $this->puts;
    }

    private function getFilePhpReturn(string $name): FilePhpReturnInterface
    {
        return new FilePhpReturn(
            new FilePhp(
                new File(
                    $this->directory->path()->getChild("{$name}.php")
                )
            )
        );
    }
}
