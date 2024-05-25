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

use Chevere\Cache\Key;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class KeyTest extends TestCase
{
    public function testInvalidArgumentConstruct()
    {
        $this->expectException(InvalidArgumentException::class);
        new Key('././\\~:');
    }

    public function testConstruct(): void
    {
        $string = 'test';
        $key = new Key($string);
        $this->assertSame($string, $key->__toString());
    }
}
