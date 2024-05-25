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

use Chevere\Parameter\Interfaces\CastInterface;
use RuntimeException;

/**
 * Describes the component that defines a cache item.
 */
interface ItemInterface
{
    /**
     * Provides access to the cache PHP variable "as-is".
     *
     * @throws RuntimeException
     */
    public function get(): mixed;

    public function cast(): CastInterface;
}
