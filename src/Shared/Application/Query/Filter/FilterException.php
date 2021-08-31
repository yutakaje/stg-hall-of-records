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

namespace Stg\HallOfRecords\Shared\Application\Query\Filter;

use Stg\HallOfRecords\Shared\Infrastructure\Locale\TranslatorInterface;

/**
 * @phpstan-import-type Parameters from TranslatorInterface
 */
final class FilterException extends \Exception
{
    /** @var Parameters */
    private array $parameters;

    /**
     * @param Parameters $parameters
     */
    public function __construct(string $message, array $parameters = [])
    {
        parent::__construct($message);
        $this->parameters = $parameters;
    }

    public static function invalidOperator(string $operator): self
    {
        return new self('errors.filter.invalid-operator', [
            '%operator%' => $operator,
        ]);
    }

    public static function invalidConjunction(string $operator): self
    {
        return new self('errors.filter.invalid-conjunction', [
            '%operator%' => $operator,
        ]);
    }

    public static function invalidFieldName(string $name): self
    {
        return new self('errors.filter.invalid-field-name', [
            '%name%' => $name,
        ]);
    }

    public static function invalidComparison(
        string $type,
        string $operator
    ): self {
        return new self("errors.filter.invalid-comparison.{$type}", [
            '%operator%' => $operator,
        ]);
    }

    /**
     * @return Parameters
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
