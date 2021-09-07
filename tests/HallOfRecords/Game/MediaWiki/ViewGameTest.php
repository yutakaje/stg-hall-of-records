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
use Tests\Helper\Data\ScoreEntries;
use Tests\Helper\Data\ScoreEntry;

class ViewGameTest extends \Tests\TestCase
{
    public function testWithEnLocale(): void
    {
        $game = $this->data()->createGame(
            $this->data()->createCompany('CAVE'),
            'Akai Katana'
        );
        $game->setScores(new ScoreEntries([
            $this->data()->createScore(
                $game,
                $this->data()->createPlayer('player2'),
                'player2',
                '992,893,110'
            ),
            $this->data()->createScore(
                $game,
                $this->data()->createPlayer('player3'),
                'player3',
                '503,434,050'
            ),
            $this->data()->createScore(
                $game,
                $this->data()->createPlayer('player1'),
                'player1',
                '234,828,910'
            ),
        ]));

        $this->testWithLocale($game, $this->locale()->get('en'));
    }

    public function testWithJaLocale(): void
    {
        $game = $this->data()->createGame(
            $this->data()->createCompany('ケイブ'),
            'エスプレイド'
        );
        $game->setScores(new ScoreEntries([
            $this->data()->createScore(
                $game,
                $this->data()->createPlayer('プレイヤー2'),
                'プレイヤー2',
                '992,893,110'
            ),
            $this->data()->createScore(
                $game,
                $this->data()->createPlayer('プレイヤー3'),
                'プレイヤー3',
                '503,434,050'
            ),
            $this->data()->createScore(
                $game,
                $this->data()->createPlayer('プレイヤー1'),
                'プレイヤー1',
                '234,828,910'
            ),
        ]));

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

    private function insertGame(GameEntry $game): void
    {
        $this->data()->insertGame($game);
        $this->data()->insertScores($game->scores()->entries());
    }

    private function createOutput(GameEntry $game, Locale $locale): string
    {
        return $this->mediaWiki()->removePlaceholders(
            $this->locale()->translate(
                $locale,
                $this->mediaWiki()->loadMainTemplate(
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

    private function createScoresOutput(
        ScoreEntries $scores,
        Locale $locale
    ): string {
        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Game', 'view-game/scores-list'),
            [
                "{{ scores|length }}" => $scores->numEntries(),
                "{{ entry|raw }}" => implode(PHP_EOL, array_map(
                    fn (ScoreEntry $score) => $this->createScoreOutput(
                        $score,
                        $locale
                    ),
                    $scores->sorted()
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
                '{{ scoreValue|raw }}' => $this->createScoreValueOutput($score),
                '{{ links.player }}' => "/{$locale}/players/{$player->id()}",
            ]
        );
    }

    private function createScoreValueOutput(ScoreEntry $score): string
    {
        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Game', 'view-game/score-value'),
            [
                '{{ score.value }}' => $score->scoreValue(),
                '{{ score.realValue }}' => '',
                '{% if score.realValue != score.value %} []{% endif %}' => '',
            ]
        );
    }
}
