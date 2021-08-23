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
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;
use Tests\Helper\Data\GameEntry;
use Tests\Helper\Data\ScoreEntry;

class ViewGameTest extends \Tests\TestCase
{
    public function testWithEnLocale(): void
    {
        $game = $this->data()->createGame(
            $this->data()->createCompany('CAVE'),
            'Akai Katana'
        );

        $this->addScores($game, [
            $this->data()->createScore(
                $game,
                $this->data()->createPlayer('Player-1'),
                'player-1',
                '234,828,910'
            ),
            $this->data()->createScore(
                $game,
                $this->data()->createPlayer('Player-2'),
                'player-2',
                '992,893,110'
            ),
        ]);

        $this->testWithLocale($game, $this->locale()->get('en'));
    }

    public function testWithJaLocale(): void
    {
        $game = $this->data()->createGame(
            $this->data()->createCompany('CAVE'),
            'Akai Katana'
        );

        $this->addScores($game, [
            $this->data()->createScore(
                $game,
                $this->data()->createPlayer('プレイヤー-1'),
                'プレイヤーx1',
                '234,828,910'
            ),
            $this->data()->createScore(
                $game,
                $this->data()->createPlayer('プレイヤー-2'),
                'プレイヤーx2',
                '992,893,110'
            ),
        ]);

        $this->testWithLocale($game, $this->locale()->get('ja'));
    }

    private function testWithLocale(GameEntry $game, Locale $locale): void
    {
        $this->insertGame($game);

        $request = $this->http()->createServerRequest(
            'GET',
            "/{$locale}/games/{$game->id()}"
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

    /**
     * @param ScoreEntry[] $scores
     */
    private function addScores(GameEntry $game, array $scores): void
    {
        // Adding scores ensures that the scores are displayed as expected.
        foreach ($scores as $score) {
            $game->addScore($score);
        }
    }

    private function insertGame(GameEntry $game): void
    {
        $this->data()->insertGame($game);
        $this->data()->insertScores($game->scores());
    }

    private function createOutput(GameEntry $game, Locale $locale): string
    {
        return $this->mediaWiki()->removePlaceholders(
            $this->locale()->translate(
                $locale,
                $this->mediaWiki()->loadBasicTemplate(
                    $this->createGameOutput($game, $locale),
                    $locale,
                    "/{locale}/games/{$game->id()}",
                )
            )
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

        return $this->data()->replace(
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
