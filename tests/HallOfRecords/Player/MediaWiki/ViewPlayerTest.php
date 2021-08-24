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
use Tests\Helper\Data\ScoreEntries;
use Tests\Helper\Data\ScoreEntry;

class ViewPlayerTest extends \Tests\TestCase
{
    public function testWithEnLocale(): void
    {
        $games = [
            $this->data()->createGame(
                $this->data()->createCompany('Tanoshimasu'),
                'Aka to Blue Type-R',
                'aka to blue type-r'
            ),
            $this->data()->createGame(
                $this->data()->createCompany('CAVE'),
                'Akai Katana',
                'akai katana'
            ),
            $this->data()->createGame(
                $this->data()->createCompany('SNK'),
                'ASO: Armored Scrum Object / Alpha Mission',
                'aso: armored scrum object / alpha mission'
            ),
            $this->data()->createGame(
                $this->data()->createCompany('Visco'),
                'Asuka & Asuka',
                'asuka & asuka'
            ),
        ];

        $player = $this->data()->createPlayer('player1');
        $player->setScores(new ScoreEntries([
            $this->data()->createScore(
                $games[0],
                $player,
                "[{$player->name()}]",
                '834,883,500'
            ),
            $this->data()->createScore(
                $games[1],
                $player,
                "[{$player->name()}]",
                '873,456,489'
            ),
            $this->data()->createScore(
                $games[2],
                $player,
                "[{$player->name()}]",
                '992,893,110'
            ),
            $this->data()->createScore(
                $games[2],
                $player,
                "[{$player->name()}]",
                '234,828,910'
            ),
            $this->data()->createScore(
                $games[3],
                $player,
                "[{$player->name()}]",
                '503,384,100'
            ),
        ]));

        $this->executeTest($player, $this->locale()->get('en'));
    }

    public function testWithJaLocale(): void
    {
        $games = [
            $this->data()->createGame(
                $this->data()->createCompany('ケイブ'),
                'エスプレイド',
                'えすぷれいど'
            ),
            $this->data()->createGame(
                $this->data()->createCompany('ケイブ'),
                'ケツイ〜絆地獄たち〜',
                'けついきずなじごくたち'
            ),
            $this->data()->createGame(
                $this->data()->createCompany('コナミ'),
                '出たな!ツインビー',
                'でたな!ついんびー',
            ),
            $this->data()->createGame(
                $this->data()->createCompany('ライジング / エイティング'),
                'バトルガレッガ',
                'ばとるがれっが'
            ),
        ];

        $player = $this->data()->createPlayer('プレイヤー-1');
        $player->setScores(new ScoreEntries([
            $this->data()->createScore(
                $games[0],
                $player,
                "[{$player->name()}]",
                '834,883,500'
            ),
            $this->data()->createScore(
                $games[1],
                $player,
                "[{$player->name()}]",
                '873,456,489'
            ),
            $this->data()->createScore(
                $games[2],
                $player,
                "[{$player->name()}]",
                '992,893,110'
            ),
            $this->data()->createScore(
                $games[2],
                $player,
                "[{$player->name()}]",
                '234,828,910'
            ),
            $this->data()->createScore(
                $games[3],
                $player,
                "[{$player->name()}]",
                '503,384,100'
            ),
        ]));

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

    private function insertPlayer(PlayerEntry $player): void
    {
        $this->data()->insertPlayer($player);
        $this->data()->insertScores($player->scores()->entries());
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

    private function createScoresOutput(
        ScoreEntries $scores,
        Locale $locale
    ): string {
        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Player', 'view-player/scores-list'),
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
        $game = $score->game();

        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Player', 'view-player/score-entry'),
            [
                '{{ score.id }}' => $score->id(),
                '{{ game.id }}' => $game->id(),
                '{{ game.name }}' => htmlentities($game->name($locale)),
                '{{ score.playerName }}' => $score->playerName(),
                '{{ score.scoreValue }}' => $score->scoreValue(),
                '{{ links.game }}' => "/{$locale}/games/{$game->id()}",
            ]
        );
    }
}
