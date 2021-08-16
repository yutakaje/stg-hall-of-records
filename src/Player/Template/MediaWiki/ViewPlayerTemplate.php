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

namespace Stg\HallOfRecords\Player\Template\MediaWiki;

use Psr\Http\Message\ResponseInterface;
use Stg\HallOfRecords\Player\Template\ViewPlayerTemplateInterface;
use Stg\HallOfRecords\Shared\Application\Query\Resource;
use Stg\HallOfRecords\Shared\Application\Query\Resources;
use Stg\HallOfRecords\Shared\Application\Query\ViewResult;
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;
use Stg\HallOfRecords\Shared\Template\MediaWiki\BasicTemplate;
use Stg\HallOfRecords\Shared\Template\MediaWiki\Routes;
use Stg\HallOfRecords\Shared\Template\Renderer;

final class ViewPlayerTemplate implements ViewPlayerTemplateInterface
{
    private Renderer $renderer;
    private BasicTemplate $wrapper;
    private Routes $routes;

    public function __construct(
        Renderer $renderer,
        BasicTemplate $wrapper,
        Routes $routes
    ) {
        $this->renderer = $renderer->withTemplateFiles(
            __DIR__ . '/html/view-player'
        );
        $this->wrapper = $wrapper;
        $this->routes = $routes;
    }

    public function respond(
        ResponseInterface $response,
        ViewResult $result
    ): ResponseInterface {
        $response->getBody()->write($this->createOutput(
            $result->resource(),
            $result->locale()
        ));
        return $response;
    }

    private function createOutput(Resource $player, Locale $locale): string
    {
        $routes = $this->routes->withLocale($locale);

        return $this->wrapper->render($locale, $this->renderPlayer(
            $this->renderer->withLocale($locale),
            $routes,
            $player
        ), ['self' => $routes->viewPlayer($player->id)]);
    }

    private function renderPlayer(
        Renderer $renderer,
        Routes $routes,
        Resource $player
    ): string {
        return $renderer->render('main', [
            'player' => $this->createPlayerVar(
                $player,
                $this->renderAliases($renderer, $player)
            ),
            'scores' => $this->renderScores($renderer, $routes, $player->scores),
        ]);
    }

    private function createPlayerVar(
        Resource $player,
        string $renderedAliases
    ): \stdClass {
        $var = new \stdClass();
        $var->id = $player->id;
        $var->name = $player->name;
        $var->aliases = $renderedAliases;

        return $var;
    }

    private function renderAliases(
        Renderer $renderer,
        Resource $player
    ): string {
        return $renderer->render('aliases-list', [
            'aliases' => $player->aliases,
        ]);
    }

    private function renderScores(
        Renderer $renderer,
        Routes $routes,
        Resources $scores
    ): string {
        return $renderer->render('scores-list', [
            'scores' => $scores->map(
                fn (Resource $score) => $this->renderScore(
                    $renderer,
                    $routes,
                    $score
                )
            ),
        ]);
    }

    private function renderScore(
        Renderer $renderer,
        Routes $routes,
        Resource $score
    ): string {
        return $renderer->render('score-entry', [
            'game' => $this->createGameVar($score),
            'score' => $this->createScoreVar($score),
            'links' => [
                'game' => $routes->viewGame($score->gameId),
            ],
        ]);
    }

    private function createGameVar(Resource $score): \stdClass
    {
        $var = new \stdClass();
        $var->id = $score->gameId;
        $var->name = $score->gameName;

        return $var;
    }

    private function createScoreVar(Resource $score): \stdClass
    {
        $var = new \stdClass();
        $var->id = $score->id;
        $var->playerName = $score->playerName;
        $var->scoreValue = $score->scoreValue;

        return $var;
    }
}
