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
use Chevere\Throwable\Exceptions\OutOfBoundsException;
use Chevere\VariableSupport\Interfaces\StorableVariableInterface;

/**
 * Describes the component in charge of caching PHP variables.
 *
 * `cached.php >>> <?php return 'my cached data';`
 */
interface CacheInterface
{
    public const ILLEGAL_KEY_CHARACTERS = '\.\/\\\~\:';

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
     * Put item in cache.
     *
     * Return an instance with the specified put.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified put.
     */
    public function withPut(KeyInterface $key, StorableVariableInterface $variable): self;

    /**
     * Remove item from cache.
     *
     * Return an instance with the specified removed.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified removed.
     *
     * @throws FileUnableToRemoveException if unable to remove the cache file
     */
    public function without(KeyInterface $key): self;

    /**
     * Indicates whether the cache exists for the given key.
     */
    public function exists(KeyInterface $key): bool;

    /**
     * Get a cache item.
     *
     * @throws OutOfBoundsException
     */
    public function get(KeyInterface $key): ItemInterface;

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
