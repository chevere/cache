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

namespace Chevere\Cache\Interfaces;

use Chevere\Filesystem\Exceptions\DirectoryUnableToCreateException;
use Chevere\Filesystem\Exceptions\FileUnableToRemoveException;
use Chevere\Filesystem\Interfaces\DirectoryInterface;
use Chevere\VarSupport\Interfaces\StorableVariableInterface;
use OutOfBoundsException;

/**
 * Describes the component in charge of caching PHP variables.
 *
 * `cached.php >>> <?php return 'my cached data';`
 */
interface CacheInterface
{
    /**
     * @param DirectoryInterface $directory Directory for working cache
     * @throws DirectoryUnableToCreateException if $dir doesn't exists and unable to create
     */
    public function __construct(DirectoryInterface $directory);

    /**
     * Provides access to the cache directory.
     */
    public function directory(): DirectoryInterface;

    /**
     * Put storable item(s) in cache.
     *
     * Return an instance with the specified put.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified put.
     */
    public function withPut(StorableVariableInterface ...$storable): self;

    /**
     * Remove item(s) from cache.
     *
     * Return an instance with the specified key removed.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified key removed.
     *
     * @throws FileUnableToRemoveException if unable to remove the cache file
     */
    public function withRemove(string ...$key): self;

    /**
     * Indicates whether the cache exists for the given key(s).
     */
    public function exists(string ...$key): bool;

    /**
     * Get a cache item.
     *
     * @throws OutOfBoundsException
     */
    public function get(string $key): ItemInterface;

    /**
     * Provides access to the array containing puts.
     *
     * ```php
     * return [
     *      'key' => [
     *              'checksum' => '<file_checksum>',
     *              'path' => '<the_file_path>'
     *      ],
     * ];
     * ```
     *
     * @return array<string, array<string, string>>
     */
    public function puts(): array;
}
