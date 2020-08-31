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

use DI\ContainerBuilder;
use Stg\HallOfRecords\MediaWikiDatabaseFetcher;
use Stg\HallOfRecords\MediaWikiGenerator;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$rootDir = dirname(__DIR__);
require "{$rootDir}/vendor/autoload.php";

try {
    $contents = fetchContents();
    $description = parseDescription($contents);
    $companies = parseCompanies($contents);

    $games = convertToGameList($companies);
    $toc = createToc($games);
    $db = convertToDatabase($description, $toc, $games);
    saveContents($db);
} catch (\Throwable $error) {
    http_response_code(500);
    // @TODO: Use Monolog or similiar.
    print_r([
        $error->getMessage(),
        $error->getTraceAsString(),
    ]);
    exit('Unexpected server error');
}

function createToc(array $allGames): string
{
    $grouped = [];

    foreach ($allGames as $game) {
        $grouped[mb_substr($game->name, 0, 1)][] = $game->name;
    }

    foreach ($grouped as $char => $games) {
        sort($grouped[$char]);
    }


    ksort($grouped);

    // Quick fix to put numbers in front.
    $last = array_pop($grouped);
    $grouped = array_merge(
        ['0-9' => $last],
        $grouped,
    );

    $tocEntries = [];
    foreach ($grouped as $char => $games) {
        $tocEntries[] = '|-';
        $tocEntries[] = "| {$char} || " . implode(' | ', array_map(
            fn (string $name) => "[[#{$name}|{$name}]]",
            $games
        ));
    }

    return implode(PHP_EOL, array_merge(
        ['{| class="wikitable"'],
        $tocEntries,
        ['|}']
    ));
}

function convertToDatabase(
    string $description,
    string $toc,
    array $games
): string {
    return globalSection($description, $toc) . PHP_EOL . implode(PHP_EOL, array_map(
        fn (\stdClass $game) => str_replace(
            [
                '{{ game-name }}',
                '{{ company-name }}',
                '{{ game-content }}',
            ],
            [
                $game->name,
                $game->company,
                str_replace(PHP_EOL, PHP_EOL . '            ', trim($game->content))
            ],
            <<<'TPL'
== {{ game-name }} ==
<div style="display:none"><nowiki>
name: "{{ game-name }}"
company: "{{ company-name }}"

layout:
    templates:
        game : |
            {{Anchor|{{ game-name }}}}
            {{ game-content }}
</nowiki></div>


TPL
        ),
        $games
    ));
}

function convertToGameList(array $companies): array
{
    $allGames = [];

    foreach ($companies as $company) {
        foreach ($company->games as $game) {
            $game->company = $company->name;
            $allGames[] = $game;
        }
    }

    return $allGames;
}

function parseGames(string $contents): array
{
    $games = [];

    $beginString = '{| class="wikitable" style="text-align: center"';

    preg_match_all('/' . preg_quote($beginString, '/') . '/u', $contents, $matches, PREG_OFFSET_CAPTURE);

    $fullMatches = $matches[0];

    foreach ($fullMatches as $index => $match) {
        $game = new \stdClass();
        $game->name = "game #{$index}";

        $startPos = $match[1] + strlen($match[0]);
        if (isset($fullMatches[$index + 1])) {
            $endPos = $fullMatches[$index + 1][1];
            $content = substr($contents, $startPos, $endPos - $startPos);
        } else {
            $content = substr($contents, $startPos);
        }

        $game->content = '{| class="wikitable" style="text-align: center"' . $content;
        $game->name = explode(PHP_EOL, $game->content)[2];
        if (($pos = strpos($game->name, '|')) !== false) {
            $game->name = trim(str_replace(['[[', ']]'], '', substr($game->name, $pos + 1)));
        }

        $games[] = $game;
    }

    return $games;
}

function parseCompanies(string $contents): array
{
    $companies = [];

    preg_match_all('/== (.*?) ==/u', $contents, $matches, PREG_OFFSET_CAPTURE);

    $fullMatches = $matches[0];

    foreach ($fullMatches as $index => $match) {
        $company = new \stdClass();
        $company->name = trim($match[0], '= ');

        $startPos = $match[1] + strlen($match[0]);
        if (isset($fullMatches[$index + 1])) {
            $endPos = $fullMatches[$index + 1][1];
            $games = substr($contents, $startPos, $endPos - $startPos);
        } else {
            $games = substr($contents, $startPos);
        }

        $company->games = parseGames($games);

        $companies[] = $company;
    }

    return $companies;
}

function parseDescription(string $contents): string
{
    $endPos = strpos($contents, '{| class="wikitable"');
    if ($endPos === false) {
        return '';
    }

    return substr($contents, 0, $endPos);
}

function fetchContents(): string
{
    return file_get_contents(__DIR__ . '/migrate.input');
}

function saveContents(string $contents): void
{
    file_put_contents(__DIR__ . '/migrate.output', $contents);
}

function globalSection(string $description, string $toc): string
{
    return str_replace(
        [
            '{# description #}',
        ],
        [
            str_replace(PHP_EOL, PHP_EOL . '    ', trim($description) . PHP_EOL . PHP_EOL . $toc),
        ],
        <<<'OUTPUT'
== Global ==
Values defined in this section will apply to all the games in the database. Its main purpose is to reduce redundant translations for reocurring values (e.g. company names, common column names, ...).

<div style="display:none"><nowiki>
name: global

description: |
    {# description #}

layout:
    sort:
        games:
            name: asc
        scores:
            score: desc

    group:
        scores:
          - ship
          - mode
          - weapon
          - version

    templates:
        main: |
            {{ description|raw }}
            __NOTOC__
            {{ include('games') }}
        games: |
            {% for game in games %}
            {% if game.template %}
            {{ game.template|raw }}

            {% else %}
            {{ include('game') }}

            {% endif %}
            {% endfor %}
        game: |
            {| class="wikitable" style="text-align: center
            |-
            ! colspan="{{ game.headers|length }}" | {{ game.properties.name }}
            |-
            ! {{ game.headers|join(' !! ') }}
            {% for columns in game.scores %}
            |-
            | {{ columns|join(' || ') }}
            {% endfor %}
            |}


translations:
  - property: company
    value: CAVE
    value-jp: ケイブ
  - property: company
    value: Raizing / 8ing
    value-jp: ライジング / エイティング

</nowiki></div>
OUTPUT
    );
}
