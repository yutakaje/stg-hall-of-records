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

namespace Stg\HallOfRecords\Export;

use Stg\HallOfRecords\Database\GameRepository;
use Stg\HallOfRecords\Database\ScoreRepository;

final class MediaWikiExporter
{
    private GameRepository $gameRepository;
    private ScoreRepository $scoreRepository;

    public function __construct(
        GameRepository $gameRepository,
        ScoreRepository $scoreRepository
    ) {
        $this->gameRepository = $gameRepository;
        $this->scoreRepository = $scoreRepository;
    }

    public function export(): string
    {
        return 'IMPLEMENT ME';
    }
}
