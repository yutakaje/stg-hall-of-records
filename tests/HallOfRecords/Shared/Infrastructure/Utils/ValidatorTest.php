<?php

/*
 * This file is part of the stg/hall-of-records package.
 *
 * (c) YTK <yutakaje@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\HallOfRecords\Shared\Infrastructure\Utils;

use Stg\HallOfRecords\Shared\Infrastructure\Utils\ValidationException;
use Stg\HallOfRecords\Shared\Infrastructure\Utils\Validator;

class ValidatorTest extends \Tests\TestCase
{
    public function testWithGoodId(): void
    {
        $expected = '40';

        self::assertSame($expected, Validator::id(40));
        self::assertSame($expected, Validator::id('40'));
    }

    public function testWithBadId(): void
    {
        $validate = function ($input): void {
            try {
                Validator::id($input);
                self::fail('Call to `id` should result in exception');
            } catch (ValidationException $exception) {
                self::succeed();
            }
        };

        $validate(' 40');
        $validate('40 ');
        $validate(' 40 ');
        $validate('a40');
        $validate('40a');
        $validate('4a0');
        $validate('4.0');
    }
}
