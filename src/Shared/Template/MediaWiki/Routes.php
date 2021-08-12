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

final class Routes
{
    private string $locale;

    public function __construct()
    {
        $this->locale = '{locale}';
    }

    public function withLocale(Locale $locale): self
    {
        $clone = clone $this;
        $clone->locale = $locale->value();

        return $clone;
    }

    public function listCompanies(): string
    {
        return "/{$this->locale}/companies";
    }

    public function viewCompany(string $id = '{id}'): string
    {
        return "/{$this->locale}/companies/{$id}";
    }

    public function listGames(): string
    {
        return "/{$this->locale}/games";
    }

    public function viewGame(string $id = '{id}'): string
    {
        return "/{$this->locale}/games/{$id}";
    }

    public function listPlayers(): string
    {
        return "/{$this->locale}/players";
    }

    public function viewPlayer(string $id = '{id}'): string
    {
        return "/{$this->locale}/players/{$id}";
    }
}
