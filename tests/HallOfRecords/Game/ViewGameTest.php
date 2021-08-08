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

namespace Tests\HallOfRecords\Game;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tests\Helper\Data\GameEntry;
use Tests\Helper\Data\ScoreEntry;

class ViewGameTest extends \Tests\TestCase
{
    public function testWithDefaultLocale(): void
    {
        $request = $this->http()->createServerRequest('GET', '/games/{id}');

        $this->testWithLocale($request, $this->locale()->default());
    }

    public function testWithRandomLocale(): void
    {
        $locale = $this->locale()->random();

        $request = $this->http()->createServerRequest('GET', '/games/{id}')
            ->withHeader('Accept-Language', $locale);

        $this->testWithLocale($request, $locale);
    }

    private function testWithLocale(
        ServerRequestInterface $request,
        string $locale
    ): void {
        $game = $this->createGameEntry();

        $this->insertGame($game);

        $request = $this->http()->replaceInUriPath(
            $request,
            '{id}',
            (string)$game->id()
        );

        $response = $this->app()->handle($request);

        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        self::assertSame(
            $this->mediaWiki()->canonicalizeHtml(
                $this->createOutput($game, $locale)
            ),
            $this->mediaWiki()->canonicalizeHtml(
                (string)$response->getBody()
            )
        );
    }

    private function createGameEntry(): GameEntry
    {
        $game = $this->data()->createGame(
            'ketsui',
            $this->data()->createCompany('cave')
        );

        // Add some scores for this game to ensure
        // that the scores are displayed as expected.
        $numScores = random_int(1, 5);
        for ($i = 0; $i < $numScores; ++$i) {
            $game->addScore($this->data()->createScore(
                $game,
                $this->data()->createPlayer('dareka'),
                '誰か',
                implode(',', [
                    random_int(100, 999),
                    random_int(100, 999),
                    random_int(100, 999),
                ])
            ));
        }

        return $game;
    }

    private function insertGame(GameEntry $game): void
    {
        $this->data()->insertGame($game);
        $this->data()->insertScores($game->scores());
    }

    private function createOutput(GameEntry $game, string $locale): string
    {
        return str_replace(
            '{{content|raw}}',
            $this->createGameOutput($game, $locale),
            $this->mediaWiki()->loadTemplate('Shared', 'basic')
        );
    }

    private function createGameOutput(
        GameEntry $game,
        string $locale
    ): string {
        $company = $game->company();

        return str_replace(
            [
                '{{ game.name }}',
                '{{ company.link }}',
                '{{ company.name }}',
                '{{ scores|raw }}',
            ],
            [
                $game->name($locale),
                "/companies/{$company->id()}",
                $company->name($locale),
                $this->createScoresOutput($game->scores(), $locale),
            ],
            $this->mediaWiki()->loadTemplate('Game', 'view-game/main')
        );
    }

    /**
     * @param ScoreEntry[] $scores
     */
    private function createScoresOutput(array $scores, string $locale): string
    {
        usort($scores, fn ($lhs, $rhs) => $lhs->scoreValue() <=> $rhs->scoreValue());
        $scores = array_reverse($scores);

        return $this->mediaWiki()->removePlaceholders(str_replace(
            [
                "{{ scores|length }}",
                "{{ entry|raw }}",
            ],
            [
                sizeof($scores),
                implode(PHP_EOL, array_map(
                    fn (ScoreEntry $score) => $this->createScoreOutput($score, $locale),
                    $scores
                )),
            ],
            $this->mediaWiki()->loadTemplate('Game', 'view-game/scores-list')
        ));
    }

    private function createScoreOutput(ScoreEntry $score, string $locale): string
    {
        return str_replace(
            [
                '{{ score.id }}',
                '{{ player.id }}',
                '{{ player.link }}',
                '{{ score.playerName }}',
                '{{ score.scoreValue }}',
            ],
            [
                $score->id(),
                $score->player()->id(),
                "/players/{$score->player()->id()}",
                $score->playerName(),
                $score->scoreValue(),
            ],
            $this->mediaWiki()->loadTemplate('Game', 'view-game/score-entry')
        );
    }
}
