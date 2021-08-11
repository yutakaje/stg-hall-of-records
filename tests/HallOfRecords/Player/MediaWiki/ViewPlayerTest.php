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
use Psr\Http\Message\ServerRequestInterface;
use Tests\Helper\Data\GameEntry;
use Tests\Helper\Data\PlayerEntry;
use Tests\Helper\Data\ScoreEntry;

class ViewPlayerTest extends \Tests\TestCase
{
    public function testWithDefaultLocale(): void
    {
        $player = $this->createPlayer();

        $request = $this->http()->createServerRequest('GET', '/players/{id}');

        $this->executeTest($player, $request, $this->locale()->default());
    }

    public function testWithRandomLocale(): void
    {
        $player = $this->createPlayer();

        $locale = $this->locale()->random();

        $request = $this->http()->createServerRequest('GET', '/players/{id}')
            ->withHeader('Accept-Language', $locale);

        $this->executeTest($player, $request, $locale);
    }

    public function testWithAliases(): void
    {
        $player = $this->createPlayer(['Reddo Arimaa', 'Red Arimer']);

        $request = $this->http()->createServerRequest('GET', '/players/{id}');

        $this->executeTest($player, $request, $this->locale()->default());
    }

    private function executeTest(
        PlayerEntry $player,
        ServerRequestInterface $request,
        string $locale
    ): void {
        $this->insertPlayer($player);

        $request = $this->http()->replaceInUriPath(
            $request,
            '{id}',
            (string)$player->id()
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
     * @param string[] $aliases
     */
    private function createPlayer(array $aliases = []): PlayerEntry
    {
        $player =  $this->data()->createPlayer('Akuma', $aliases);

        $companies = [
            $this->data()->createCompany('konami'),
            $this->data()->createCompany('cave'),
        ];

        $games = [
            $this->data()->createGame($companies[1], 'Ketsui'),
            $this->data()->createGame($companies[1], 'Esprade'),
            $this->data()->createGame($companies[0], 'Detana! TwinBee'),
        ];
        $randomGame = fn () => $games[random_int(0, 2)];

        // Add some scores for this game to ensure
        // that the scores are displayed as expected.
        $numScores = random_int(1, 5);
        for ($i = 0; $i < $numScores; ++$i) {
            $player->addScore($this->data()->createScore(
                $randomGame(),
                $player,
                '誰か' . random_int(10, 99),
                implode(',', [
                    random_int(100, 999),
                    random_int(100, 999),
                    random_int(100, 999),
                ])
            ));
        }

        return $player;
    }

    private function insertPlayer(PlayerEntry $player): void
    {
        $this->data()->insertPlayer($player);
        $this->data()->insertScores($player->scores());
    }

    private function createOutput(PlayerEntry $player, string $locale): string
    {
        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Shared', 'basic'),
            [
                '{{content|raw}}' => $this->createPlayerOutput($player, $locale),
            ]
        );
    }

    private function createPlayerOutput(
        PlayerEntry $player,
        string $locale
    ): string {
        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Player', 'view-player/main'),
            [
                '{{ player.id }}' => $player->id(),
                '{{ player.name }}' => $player->name(),
                '{{ player.link }}' => "/players/{$player->id()}",
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
        string $locale
    ): string {
        if ($aliases == null) {
            return '';
        }

        sort($aliases);

        return $this->mediaWiki()->removePlaceholders(
            $this->data()->replace(
                $this->mediaWiki()->loadTemplate('Player', 'view-player/aliases-list'),
                [
                    "{{ aliases|join(', ') }}" => implode(', ', $aliases),
                ]
            )
        );
    }

    /**
     * @param ScoreEntry[] $scores
     */
    private function createScoresOutput(array $scores, string $locale): string
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

        return $this->mediaWiki()->removePlaceholders(
            $this->data()->replace(
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
            )
        );
    }

    private function createScoreOutput(ScoreEntry $score, string $locale): string
    {
        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Player', 'view-player/score-entry'),
            [
                '{{ score.id }}' => $score->id(),
                '{{ game.id }}' => $score->game()->id(),
                '{{ game.name }}' => $score->game()->name($locale),
                '{{ game.link }}' => "/games/{$score->game()->id()}",
                '{{ score.playerName }}' => $score->playerName(),
                '{{ score.scoreValue }}' => $score->scoreValue(),
            ]
        );
    }
}
