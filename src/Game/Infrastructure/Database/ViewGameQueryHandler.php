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

namespace Stg\HallOfRecords\Game\Infrastructure\Database;

use Doctrine\DBAL\Connection;
use Stg\HallOfRecords\Game\Application\Query\ViewGameQueryHandlerInterface;
use Stg\HallOfRecords\Shared\Application\Query\Resource;
use Stg\HallOfRecords\Shared\Application\Query\Resources;
use Stg\HallOfRecords\Shared\Application\Query\ViewQuery;
use Stg\HallOfRecords\Shared\Application\Query\ViewResult;
use Stg\HallOfRecords\Shared\Infrastructure\Error\ResourceNotFoundException;
use Symfony\Component\Yaml\Yaml;

final class ViewGameQueryHandler implements ViewGameQueryHandlerInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(ViewQuery $query): ViewResult
    {
        $game = $this->readGame($query);
        $game->scores = $this->readScores($query);

        return new ViewResult($game);
    }

    private function readGame(ViewQuery $query): Resource
    {
        $qb = $this->connection->createQueryBuilder();

        $stmt = $qb->select(
            'id',
            'name',
            'company_id',
            'company_name',
            'description',
            'links'
        )
            ->from('stg_query_games')
            ->where($qb->expr()->and(
                $qb->expr()->eq('id', ':id'),
                $qb->expr()->eq('locale', ':locale')
            ))
            ->setParameter('id', $query->id())
            ->setParameter('locale', $query->locale()->value())
            ->executeQuery();

        $row = $stmt->fetchAssociative();

        if ($row === false) {
            throw new ResourceNotFoundException('Game not found');
        }

        return $this->createGame($row);
    }

    private function readScores(ViewQuery $query): Resources
    {
        $qb = $this->connection->createQueryBuilder();

        $stmt = $qb->select(
            'id',
            'player_id',
            'player_name',
            'score_value',
            'score_value_real',
            'sources'
        )
            ->from('stg_query_scores')
            ->where($qb->expr()->and(
                $qb->expr()->eq('game_id', ':gameId'),
                $qb->expr()->eq('locale', ':locale')
            ))
            ->setParameter('gameId', $query->id())
            ->setParameter('locale', $query->locale()->value())
            ->orderBy('score_value_sort', 'desc')
            ->addOrderBy('id')
            ->executeQuery();

        $scores = [];

        while (($row = $stmt->fetchAssociative()) !== false) {
            $scores[] = $this->createScore($row);
        }

        return new Resources($scores);
    }

    /**
     * @param Row $row
     */
    private function createGame(array $row): Resource
    {
        $game = new Resource();
        $game->id = $row['id'];
        $game->name = $row['name'];
        $game->company = $this->createCompany($row);
        $game->description = $row['description'];
        $game->links = $this->createLinks($row['links']);

        return $game;
    }

    /**
     * @param Row $row
     */
    private function createCompany(array $row): Resource
    {
        $company = new Resource();
        $company->id = $row['company_id'];
        $company->name = $row['company_name'];

        return $company;
    }

    /**
     * @param Row $row
     */
    private function createScore(array $row): Resource
    {
        $score = new Resource();
        $score->id = $row['id'];
        $score->playerId = $row['player_id'];
        $score->playerName = $row['player_name'];
        $score->scoreValue = $row['score_value'];
        $score->realScoreValue = $row['score_value_real'];
        $score->sources = $this->createSources($row['sources']);

        return $score;
    }

    private function createLinks(string $links): Resources
    {
        return new Resources(array_map(
            fn (array $link) => $this->createLink($link),
            Yaml::parse($links)
        ));
    }

    /**
     * @param array<string,string> $link
     */
    private function createLink(array $link): Resource
    {
        $resource = new Resource();
        $resource->url = $link['url'];
        $resource->title = $link['title'];

        return $resource;
    }

    private function createSources(string $sources): Resources
    {
        return new Resources(array_map(
            fn (array $source) => $this->createSource($source),
            Yaml::parse($sources)
        ));
    }

    /**
     * @param array<string,string> $source
     */
    private function createSource(array $source): Resource
    {
        $resource = new Resource();
        $resource->name = $source['name'];
        $resource->date = $source['date'];
        $resource->url = $source['url'];

        return $resource;
    }
}
