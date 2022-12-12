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

namespace Chevere\Tests\src;

use function Chevere\Filesystem\directoryForPath;
use Chevere\Filesystem\Interfaces\DirectoryInterface;
use PHPUnit\Framework\TestCase;
use ReflectionObject;

final class DirectoryHelper
{
    private DirectoryInterface $directory;

    public function __construct(TestCase $object)
    {
        $reflection = new ReflectionObject($object);
        $dirName = dirname($reflection->getFileName());
        $shortName = $reflection->getShortName();
        $this->directory = directoryForPath("${dirName}/_resources/${shortName}/");
    }

    public function directory(): DirectoryInterface
    {
        return $this->directory;
    }
}
