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

namespace Stg\HallOfRecords\Export\MediaWiki;

use Stg\HallOfRecords\Data\Game\Game;
use Stg\HallOfRecords\Data\Setting\Settings;

final class GameVariable extends \stdClass
{
    /**
     * @param ScoreVariable[] $scores
     */
    public function __construct(
        Game $game,
        Layout $layout,
        Settings $settings,
        array $scores
    ) {
        $emptyColumnRemover = new EmptyColumnRemover($scores);
        $columns = $emptyColumnRemover->remove($layout->columns());
        $scores = $emptyColumnRemover->scores();

        $this->properties = $game->properties();
        $this->scores = $scores;
        $this->links = $settings->get('links', []);
        $this->template = $layout->template('game');
        $this->headers = array_map(
            fn (array $column) => $column['label'] ?? '',
            $columns
        );
    }
}
