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

namespace Stg\HallOfRecords\Shared\Infrastructure\Utils;

use Nette\Utils\AssertionException;
use Nette\Utils\Validators;

final class Validator
{
    /**
     * @param mixed $id
     */
    public static function id($id): string
    {
        try {
            Validators::assert($id, 'numericint', 'id');
        } catch (AssertionException $exception) {
            throw new ValidationException($exception->getMessage());
        }

        return (string)$id;
    }
}
