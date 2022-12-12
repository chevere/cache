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

use Chevere\Cache\Interfaces\ItemInterface;
use Chevere\Filesystem\Interfaces\FilePhpReturnInterface;

final class Item implements ItemInterface
{
    public function __construct(
        private FilePhpReturnInterface $filePhpReturn
    ) {
    }

    public function raw(): mixed
    {
        return $this->filePhpReturn->raw();
    }

    public function variable(): mixed
    {
        return $this->filePhpReturn->variable();
    }

    public function variableArray(): array
    {
        return $this->variable();
    }

    public function variableBoolean(): bool
    {
        return $this->variable();
    }

    public function variableFloat(): float
    {
        return $this->variable();
    }

    public function variableInteger(): int
    {
        return $this->variable();
    }

    public function variableString(): string
    {
        return $this->variable();
    }
}
