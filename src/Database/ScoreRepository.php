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

namespace Stg\HallOfRecords\Database;

use Stg\HallOfRecords\Data\Game;
use Stg\HallOfRecords\Data\Score;
use Stg\HallOfRecords\Data\ScoreRepositoryInterface;
use Stg\HallOfRecords\Data\Scores;

final class ScoreRepository extends AbstractRepository implements ScoreRepositoryInterface
{
    /**
     * @param array<string,mixed> $sort
     */
    public function filterByGame(Game $game, array $sort = []): Scores
    {
        $qb = $this->connection()->createQueryBuilder();

        $columns = [
            'id',
            'player',
            'score',
            'ship',
            'mode',
            'weapon',
            'scored_date',
            'source',
            'comments',
        ];

        $stmt = $qb->select(...$columns)
            ->from('scores')
            ->where($qb->expr()->eq('game_id', ':gameId'))
            ->setParameter(':gameId', $game->id());

        foreach ($sort as $name => $order) {
            if (in_array($name, $columns, true)) {
                $this->addOrderBy($qb, $name, $order);
            }
        }

        // Sort by id to always get a distinct order.
        $stmt = $qb->addOrderBy('id')
            ->execute();

        $scores = [];

        while (($columns = $stmt->fetch()) !== false) {
            $scores[] = new Score(
                (int)$columns['id'],
                $game->id(),
                $columns['player'],
                $columns['score'],
                $columns['ship'],
                $columns['mode'],
                $columns['weapon'],
                $columns['scored_date'],
                $columns['source'],
                json_decode($columns['comments']),
            );
        }

        return new Scores($scores);
    }

    public function add(Score $score): void
    {
        $this->connection()->createQueryBuilder()
            ->insert('scores')
            ->values([
                'id' => ':id',
                'game_id' => ':gameId',
                'player' => ':player',
                'score' => ':score',
                'ship' => ':ship',
                'mode' => ':mode',
                'weapon' => ':weapon',
                'scored_date' => ':scoredDate',
                'source' => ':source',
                'comments' => ':comments',
            ])
            ->setParameter(':id', $score->id())
            ->setParameter(':gameId', $score->gameId())
            ->setParameter(':player', $score->player())
            ->setParameter(':score', $score->score())
            ->setParameter(':ship', $score->ship())
            ->setParameter(':mode', $score->mode())
            ->setParameter(':weapon', $score->weapon())
            ->setParameter(':scoredDate', $score->scoredDate())
            ->setParameter(':source', $score->source())
            ->setParameter(':comments', json_encode($score->comments()))
            ->execute();
    }
}
