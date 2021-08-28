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

namespace Stg\HallOfRecords\Shared\Template\MediaWiki;

use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;

final class SharedTemplates
{
    private ?Locale $locale;
    private BasicTemplate $basic;

    public function __construct(
        BasicTemplate $basic
    ) {
        $this->locale = null;
        $this->basic = $basic;
    }

    public function withLocale(Locale $locale): self
    {
        $clone = clone $this;
        $clone->locale = $locale;

        return $clone;
    }

    private function locale(): Locale
    {
        if ($this->locale === null) {
            throw new \LogicException('Locale must be set before usage');
        }

        return $this->locale;
    }

    /**
     * @param array<string,string> $selfLinks
     */
    public function main(string $content, array $selfLinks): string
    {
        return $this->basic->render($this->locale(), $content, $selfLinks);
    }
}
