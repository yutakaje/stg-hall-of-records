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
use Tests\Helper\Data\PlayerEntry;
use Tests\Helper\Data\ScoreEntries;

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

        $this->insertPlayer($player);

        $this->executeTest(
            'view-player.output.en',
            $this->locale()->get('en'),
            $player->id()
        );
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

        $this->insertPlayer($player);

        $this->executeTest(
            'view-player.output.ja',
            $this->locale()->get('ja'),
            $player->id()
        );
    }

    public function testWithAliases(): void
    {
        $player =  $this->data()->createPlayer('Akuma', [
            'Reddo Arimaa',
            'Red Arimer',
        ]);

        $this->insertPlayer($player);

        $this->executeTest(
            'view-player.output.en.alias',
            $this->locale()->get('en'),
            $player->id()
        );
    }

    private function executeTest(
        string $expectedOutputFile,
        Locale $locale,
        int $playerId
    ): void {
        $request = $this->http()->createServerRequest(
            'GET',
            "/{$locale}/players/{$playerId}"
        );

        $response = $this->app()->handle($request);

        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        self::assertSame(
            $this->createOutput(
                $this->filesystem()->loadFile(
                    __DIR__ . "/view-player/{$expectedOutputFile}"
                ),
                $locale,
                $playerId
            ),
            (string)$response->getBody()
        );
    }

    private function createOutput(
        string $content,
        Locale $locale,
        int $playerId
    ): string {
        return $this->mediaWiki()->removePlaceholders(
            $this->locale()->translate(
                $locale,
                $this->mediaWiki()->loadMainTemplate(
                    $content,
                    $locale,
                    "/{locale}/players/{$playerId}",
                )
            )
        );
    }

    private function insertPlayer(PlayerEntry $player): void
    {
        $this->data()->insertPlayer($player);
        $this->data()->insertScores($player->scores()->entries());
    }
}
