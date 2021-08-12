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
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;
use Tests\Helper\Data\GameEntry;
use Tests\Helper\Data\ScoreEntry;

class ViewGameTest extends \Tests\TestCase
{
    public function testWithDefaultLocale(): void
    {
        $locale = $this->locale()->default();

        $request = $this->http()->createServerRequest('GET', "/{$locale}/games/{id}");

        $this->testWithLocale($request, $locale);
    }

    public function testWithRandomLocale(): void
    {
        $locale = $this->locale()->random();

        $request = $this->http()->createServerRequest('GET', "/{$locale}/games/{id}")
            ->withHeader('Accept-Language', $locale->value());

        $this->testWithLocale($request, $locale);
    }

    private function testWithLocale(
        ServerRequestInterface $request,
        Locale $locale
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

    private function createOutput(GameEntry $game, Locale $locale): string
    {
        return $this->mediaWiki()->loadBasicTemplate(
            $this->createGameOutput($game, $locale),
            $locale
        );
    }

    private function createGameOutput(
        GameEntry $game,
        Locale $locale
    ): string {
        $company = $game->company();

        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Game', 'view-game/main'),
            [
                '{{ game.name }}' => $game->name($locale),
                '{{ company.name }}' => $company->name($locale),
                '{{ scores|raw }}' => $this->createScoresOutput(
                    $game->scores(),
                    $locale
                ),
                '{{ links.game }}' => "/{$locale}/games/{$game->id()}",
                '{{ links.company }}' => "/{$locale}/companies/{$company->id()}",
            ]
        );
    }

    /**
     * @param ScoreEntry[] $scores
     */
    private function createScoresOutput(array $scores, Locale $locale): string
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

    private function createScoreOutput(ScoreEntry $score, Locale $locale): string
    {
        $player = $score->player();

        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Game', 'view-game/score-entry'),
            [
                '{{ score.id }}' => $score->id(),
                '{{ player.id }}' => $player->id(),
                '{{ score.playerName }}' => $score->playerName(),
                '{{ score.scoreValue }}' => $score->scoreValue(),
                '{{ links.player }}' => "/{$locale}/players/{$player->id()}",
            ]
        );
    }
}
