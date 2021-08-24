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

namespace Tests\HallOfRecords\Player\MediaWiki;

use Fig\Http\Message\StatusCodeInterface;
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;
use Tests\Helper\Data\GameEntry;
use Tests\Helper\Data\PlayerEntry;
use Tests\Helper\Data\ScoreEntry;

class ViewPlayerTest extends \Tests\TestCase
{
    public function testWithEnLocale(): void
    {
        $companies = [
            $this->data()->createCompany('company1'),
            $this->data()->createCompany('company2'),
        ];

        $games = [
            $this->data()->createGame($companies[0], 'game1'),
            $this->data()->createGame($companies[1], 'game2'),
            $this->data()->createGame($companies[1], 'game3'),
        ];

        $player = $this->data()->createPlayer('Player-1');

        $this->addScores($player, [
            $this->data()->createScore(
                $games[2],
                $player,
                'player-1',
                '234,828,910'
            ),
            $this->data()->createScore(
                $games[2],
                $player,
                'player-1',
                '992,893,110'
            ),
            $this->data()->createScore(
                $games[0],
                $player,
                'player-1',
                '834,883,500'
            ),
            $this->data()->createScore(
                $games[1],
                $player,
                'player-1',
                '873,456,489'
            ),
        ]);

        $this->executeTest($player, $this->locale()->get('en'));
    }

    public function testWithJaLocale(): void
    {
        $companies = [
            $this->data()->createCompany('company1'),
            $this->data()->createCompany('company2'),
        ];

        $games = [
            $this->data()->createGame($companies[0], 'game1'),
            $this->data()->createGame($companies[1], 'game2'),
            $this->data()->createGame($companies[1], 'game3'),
        ];

        $player = $this->data()->createPlayer('プレイヤー-1');

        $this->addScores($player, [
            $this->data()->createScore(
                $games[2],
                $player,
                'player-1',
                '234,828,910'
            ),
            $this->data()->createScore(
                $games[2],
                $player,
                'player-1',
                '992,893,110'
            ),
            $this->data()->createScore(
                $games[0],
                $player,
                'player-1',
                '834,883,500'
            ),
            $this->data()->createScore(
                $games[1],
                $player,
                'player-1',
                '873,456,489'
            ),
        ]);

        $this->executeTest($player, $this->locale()->get('ja'));
    }

    public function testWithAliases(): void
    {
        // Index represents expected sort order.
        $player =  $this->data()->createPlayer('Akuma', [
            1 => 'Reddo Arimaa',
            0 => 'Red Arimer'
        ]);

        $this->executeTest($player, $this->locale()->get('en'));
    }

    private function executeTest(
        PlayerEntry $player,
        Locale $locale
    ): void {
        $this->insertPlayer($player);

        $request = $this->http()->createServerRequest(
            'GET',
            "/{$locale}/players/{$player->id()}"
        );

        $response = $this->app()->handle($request);

        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        self::assertSame(
            $this->mediaWiki()->canonicalizeHtml(
                $this->createOutput($player, $locale)
            ),
            $this->mediaWiki()->canonicalizeHtml(
                (string)$response->getBody()
            )
        );
    }

    /**
     * @param ScoreEntry[] $scores
     */
    private function addScores(PlayerEntry $player, array $scores): void
    {
        // Adding scores ensures that the scores are displayed as expected.
        foreach ($scores as $score) {
            $player->addScore($score);
        }
    }

    private function insertPlayer(PlayerEntry $player): void
    {
        $this->data()->insertPlayer($player);
        $this->data()->insertScores($player->scores());
    }

    private function createOutput(PlayerEntry $player, Locale $locale): string
    {
        return $this->mediaWiki()->removePlaceholders(
            $this->locale()->translate(
                $locale,
                $this->mediaWiki()->loadBasicTemplate(
                    $this->createPlayerOutput($player, $locale),
                    $locale,
                    "/{locale}/players/{$player->id()}",
                )
            )
        );
    }

    private function createPlayerOutput(
        PlayerEntry $player,
        Locale $locale
    ): string {
        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Player', 'view-player/main'),
            [
                '{{ player.id }}' => $player->id(),
                '{{ player.name }}' => $player->name(),
                '{{ player.aliases|raw }}' => $this->createAliasesOutput(
                    $player->aliases(),
                    $locale
                ),
                '{{ scores|raw }}' => $this->createScoresOutput(
                    $player->scores(),
                    $locale
                ),
            ]
        );
    }

    /**
     * @param string[] $aliases
     */
    private function createAliasesOutput(
        array $aliases,
        Locale $locale
    ): string {
        if ($aliases == null) {
            return '';
        }

        ksort($aliases);

        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Player', 'view-player/aliases-list'),
            [
                "{{ aliases|join(', ') }}" => implode(', ', $aliases),
            ]
        );
    }

    /**
     * @param ScoreEntry[] $scores
     */
    private function createScoresOutput(array $scores, Locale $locale): string
    {
        usort($scores, function (ScoreEntry $lhs, ScoreEntry $rhs) use ($locale): int {
            if ($lhs->game()->name($locale) !== $rhs->game()->name($locale)) {
                return $lhs->game()->name($locale) <=> $rhs->game()->name($locale);
            } elseif ($lhs->game()->id() !== $rhs->game()->id()) {
                return $lhs->game()->id() <=> $rhs->game()->id();
            } elseif ($lhs->scoreValue() !== $rhs->scoreValue()) {
                return $rhs->scoreValue() <=> $lhs->scoreValue();
            } else {
                return $lhs->id() <=> $rhs->id();
            }
        });

        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Player', 'view-player/scores-list'),
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
        $game = $score->game();

        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Player', 'view-player/score-entry'),
            [
                '{{ score.id }}' => $score->id(),
                '{{ game.id }}' => $game->id(),
                '{{ game.name }}' => $game->name($locale),
                '{{ score.playerName }}' => $score->playerName(),
                '{{ score.scoreValue }}' => $score->scoreValue(),
                '{{ links.game }}' => "/{$locale}/games/{$game->id()}",
            ]
        );
    }
}
