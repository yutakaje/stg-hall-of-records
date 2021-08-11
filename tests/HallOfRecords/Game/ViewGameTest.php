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
            $this->data()->createCompany('cave'),
            'ketsui'
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
        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Shared', 'basic'),
            [
                '{{content|raw}}' => $this->createGameOutput($game, $locale),
            ]
        );
    }

    private function createGameOutput(
        GameEntry $game,
        string $locale
    ): string {
        $company = $game->company();

        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Game', 'view-game/main'),
            [
                '{{ game.name }}' => $game->name($locale),
                '{{ company.link }}' => "/companies/{$company->id()}",
                '{{ company.name }}' => $company->name($locale),
                '{{ scores|raw }}' => $this->createScoresOutput(
                    $game->scores(),
                    $locale
                ),
            ]
        );
    }

    /**
     * @param ScoreEntry[] $scores
     */
    private function createScoresOutput(array $scores, string $locale): string
    {
        usort($scores, fn ($lhs, $rhs) => $rhs->scoreValue() <=> $lhs->scoreValue());

        return $this->mediaWiki()->removePlaceholders(
            $this->data()->replace(
                $this->mediaWiki()->loadTemplate('Game', 'view-game/scores-list'),
                [
                    "{{ scores|length }}" => sizeof($scores),
                    "{{ entry|raw }}" => implode(PHP_EOL, array_map(
                        fn (ScoreEntry $score) => $this->createScoreOutput(
                            $score,
                            $locale
                        ),
                        $scores
                    )),
                ]
            )
        );
    }

    private function createScoreOutput(ScoreEntry $score, string $locale): string
    {
        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Game', 'view-game/score-entry'),
            [
                '{{ score.id }}' => $score->id(),
                '{{ player.id }}' => $score->player()->id(),
                '{{ player.link }}' => "/players/{$score->player()->id()}",
                '{{ score.playerName }}' => $score->playerName(),
                '{{ score.scoreValue }}' => $score->scoreValue(),
            ]
        );
    }
}
