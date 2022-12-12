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

    public function get(): mixed
    {
        return $this->filePhpReturn->get();
    }

    public function getArray(): array
    {
        return $this->filePhpReturn->getArray();
    }

    public function getBoolean(): bool
    {
        return $this->filePhpReturn->getBoolean();
    }

    public function getFloat(): float
    {
        return $this->filePhpReturn->getFloat();
    }

    public function getInteger(): int
    {
        return $this->filePhpReturn->getInteger();
    }

    public function getString(): string
    {
        return $this->filePhpReturn->getString();
    }

    public function getObject(): object
    {
        return $this->filePhpReturn->getObject();
    }
}
