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

final class Routes
{
    public function listCompanies(): string
    {
        return '/companies';
    }

    public function viewCompany(string $id = '{id}'): string
    {
        return "/companies/{$id}";
    }

    public function listGames(): string
    {
        return '/games';
    }

    public function viewGame(string $id = '{id}'): string
    {
        return "/games/{$id}";
    }
}
