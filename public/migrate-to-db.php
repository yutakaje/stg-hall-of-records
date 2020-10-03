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

$rootDir = dirname(__DIR__);
require "{$rootDir}/vendor/autoload.php";

try {
    $contents = fetchContents();
    $description = parseDescription($contents);
    $companies = parseCompanies($contents);

    $games = convertToGameList($companies);
    //$toc = createToc($games);
    //$toc = createBetterToc($games);
    $toc = '';

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

function createBetterToc(array $allGames): string
{
    $grouped = [];

    foreach ($allGames as $game) {
        $grouped[mb_substr($game->name, 0, 1)][] = $game;
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
            fn (\stdClass $game) => "[[#{$game->name}|{$game->name}]]",
            $games
        ));
    }


    return str_replace(
        [
            '{{ headers }}',
            '{{ game-entries }}',
        ],
        [
            implode(PHP_EOL . '    ', array_map(
                fn (string $char, $games) => str_replace(
                    [
                        '{{ char }}',
                        '{{ classes }}',
                    ],
                    [
                        $char,
                        implode(' ', array_map(
                            fn (\stdClass $game) => "mw-customtoggle-{$game->id}",
                            $games
                        ))
                    ],
                    '<th class="{{ classes }}" style="padding-left:10px;padding-right:10px;">{{ char }}</th>'
                ),
                array_keys($grouped),
                $grouped
            )),
            implode(PHP_EOL, array_map(
                fn (\stdClass $game) => str_replace(
                    [
                        '{{ id }}',
                        '{{ name }}',
                        '{{ colspan }}',
                    ],
                    [
                        $game->id,
                        $game->name,
                        sizeof($grouped),
                    ],
                    <<<'HTML'
  <tr id="mw-customcollapsible-{{ id }}" class="mw-collapsible mw-collapsed">
    <td colspan="{{ colspan }}">[[#{{ name }}|{{ name }}]]</td>
  </tr>
HTML
                ),
                $allGames
            ))
        ],
        <<<'HTML'
<table class="wikitable">
  <tr>
    {{ headers }}
  </tr>
{{ game-entries }}
</table>
HTML
    );
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
    $addCompanyName = function (\stdClass $game): string {
        $content = str_replace(PHP_EOL, PHP_EOL . '            ', trim($game->content));

        $pos = strpos($content, ']]');
        if ($pos === false) {
            return $content;
        }

        $pos = strpos($content, "\n", $pos);
        if ($pos === false) {
            return $content;
        }

        return substr($content, 0, $pos)
            . " ({$game->company})"
            . substr($content, $pos);
    };

    return introduction() . PHP_EOL .
        globalSection($description, $toc) . PHP_EOL .
        '== Games ==' . PHP_EOL .
        implode(PHP_EOL, array_map(
            fn (\stdClass $game) => str_replace(
                [
                    '{{ game-name }}',
                    '{{ company-name }}',
                    '{{ game-content }}',
                ],
                [
                    $game->name,
                    $game->company,
                    $addCompanyName($game),
                ],
                <<<'TPL'
=== {{ game-name }} ===
<pre><nowiki>
name: "{{ game-name }}"
company: "{{ company-name }}"
needs-work: true

layout:
    templates:
        game: |
            {{Anchor|{{ game-name }}}}
            {{ game-content }}
</nowiki></pre>


TPL
            ),
            $games
        ));
}

function convertToGameList(array $companies): array
{
    $allGames = [];

    $id = 1;
    foreach ($companies as $company) {
        foreach ($company->games as $game) {
            $game->id = $id++;
            $game->company = $company->name;
            $allGames[] = $game;
        }
    }

    usort(
        $allGames,
        fn (\stdClass $lhs, \stdClass $rhs) => strtolower($lhs->name) <=> strtolower($rhs->name)
    );

    return $allGames;
}

function parseGames(string $contents): array
{
    $games = [];

    $beginString = '{| class="wikitable" style="';

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

        //$game->content = '{| class="wikitable" style="text-align: center"' . $content;
        $game->content = $beginString . $content;
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
== Global settings ==
<pre><nowiki>
name: global

# Main description in wiki syntax, available as variable `description` within the templates.
description: |
    {# description #}

# Layout information applicable to the whole page or all the games in the database.
layout:

    # Sort order for the games within the page or the scores within each game respectively.
    sort:
        games:
            name: asc
        scores:
            score: desc

    # Properties to group scores by. Every combination of these properties forms a category within a game.
    # Example: If grouped by ship only, all the scores achieved with the same ship are put into the same
    # category no matter the mode, weapon or any property.
    group:
        scores:
          - ship
          - mode
          - weapon
          - version

    # Wiki templates. The one named `main` is the entry point, everything else gets included from there.
    templates:
        main: |
            {{ description|raw }}
            {{ include('toc') }}
            {{ include('games') }}
        toc: |
            <table class="wikitable">
              <tr>
            {% set numNonEmptyGroups = 0 %}
            {% for group in games.grouped.byInitials %}
            {% if group.games %}
                <th class="{% for game in group.games %}mw-customtoggle-{{ game.properties.id }} {% endfor %}" style="padding-left:10px;padding-right:10px;">{{ group.title }}</th>
            {% set numNonEmptyGroups = numNonEmptyGroups + 1 %}
            {% endif %}
            {% endfor %}
              </tr>
            {% for group in games.grouped.byInitials %}
            {% for game in group.games %}
              <tr id="mw-customcollapsible-{{ game.properties.id }}" class="mw-collapsible mw-collapsed">
                <td colspan="{{ numNonEmptyGroups }}">[[#{{ game.properties.name }}|{{ game.properties.name }}]]</td>
              </tr>
            {% endfor %}
            {% endfor %}
            </table>
            __NOTOC__
        games: |
            {% for game in games.all %}
            {% if game.template %}
            {{ game.template|raw }}
            {% else %}
            {{ include('game') }}
            {% endif %}

            {% endfor %}
        game: |
            {| class="wikitable" style="text-align: center"
            |-
            ! colspan="{{ game.headers|length }}" | {{ game.properties.name }}
            |-
            ! {{ game.headers|join(' !! ') }}
            {% for score in game.scores %}
            {{ include('score') }}
            {% endfor %}
            |}

            {% if game.properties.description %}
            {{ game.properties.description }}

            {% endif %}
            {% if game.links %}
            {% for link in game.links %}
            * [{{ link.url}} {{link.title}}]
            {% endfor %}
            {% endif %}
        score: |
            |-
            | {% for column in score.columns %}{% if column.attrs %}{{ column.attrs|raw }} | {% endif %}{{ column.value }}{% if not loop.last %} || {% endif %}{% endfor %}

# Common translations applicable to all games in the database.
translations:
  - property: company
    value: CAVE
    value-jp: ケイブ
  - property: company
    value: Raizing / 8ing
    value-jp: ライジング / エイティング
</nowiki></pre>
OUTPUT
    );
}

function introduction(): string
{
    return <<<'OUTPUT'
'''Attention: This page serves as a database and is not meant to be consumed by end users. If you came here for the STG Hall of Records, follow one of these links:'''

* English version: https://shmups.wiki/library/STG_Hall_of_Records
* 日本語版: https://shmups.wiki/library/STG記録の殿堂

== Introduction ==
The contents of this page serve as a database and are meant to be put into a [https://shmups.wiki/records/ generator]. The generator converts the raw data, written in YAML, into actual wiki pages for different languages.

=== Structure ===
Every section of data is enclosed in &lt;nowiki&gt;&lt;/nowiki&gt; tags. Everything outside outside of these tags will be ignored by the parser. The first section is named ''global'' and contains settings for the whole page, all the remaining sections each contain data about a single game.

For every property, another one with the same name and a locale suffix can be specified. Its value will be used on the page in the corresponding language instead of the value of the original property. Currently only ''-jp'' for Japanese is used but this can be extended to support more languages.

==== Global settings ====
Values defined in this section apply to all the games in the database or the whole page in general (e.g. description, sorting of games, common translations, ...). For further information, please refer to the [[#Global settings-value|actual value]], most of the settings are documented.

==== Game ====
For each game the following properties are available:

<pre>
name: Name of the game
name-jp: 日本語のタイトル / Japanese title in kanji
name-kana: にほんごのたいとる / Japanese title in hiragana
company: Developer / Publisher

scores: Score entries (see section below)

links:
  - url: https://example.org/the-link
    title: Title for the link
  - url: https://example.org/other-link
    title: Second link title

description: "Description containing notes about counterstops and other noteworthy stuff."

layout: Layout information (see below)

# Game specific translations, applicable to all scores within the game.
translations:
  - property: mode
    value: Original
    value-jp: オリジナルモード
  - property: mode
    value: Maniac
    value-jp: マニアックモード
</pre>

===== Score =====
For each score the properties listed below are available. They are heavily game-dependent and usually not all of them are useful. Unnecessary properties can (and should) be omitted.

<pre>
player: Name of the player (required)
score: Score (required)
ship: Selected ship / character
mode: Game mode / difficulty
weapon: Weapon / style
version: Version (1P, 2P, Japan, World, Old, New, ...)
autofire: Flag whether autofire was used or not
scored-date: Date the score was achieved (YYYY-MM-DD|YYYY-MM|YYYY)
published-date: Date the score was published (YYYY-MM-DD|YYYY-MM|YYYY)
source: Information source
added-date: Date the score was added to the HoR (YYYY-MM-DD|YYYY-MM|YYYY) (required)
comments: List of additional comments
links: List of links to replay, Blog, ...
image-url: URL pointing to a screenshot of the score
</pre>

Additional properties may be specified. In this case the generator will treat their values verbatim.

===== Example =====
<pre>
&lt;pre&gt;&lt;nowiki&gt;
name: "Ketsui: Kizuna Jigoku Tachi"
name-jp: ケツイ ～絆地獄たち～
name-kana: けつい ～きずなじごくたち～
company: Cave

scores:
  - player: SPS
    score: 507,780,433
    ship: Type A
    mode: Omote
    scored-date: "2014-08"
    source: Arcadia August 2014
    added-date: "2020-06-14"
    comments:

  - player: SPS
    score: 481,402,383
    ship: Type B
    mode: Omote
    scored-date: "2014-11"
    source: Arcadia November 2014
    added-date: "2020-06-13"
    comments:
      - 6L 0B remaining
      - 1st loop 276m

  - player: SPS
    score: 489,893,348
    ship: Type A
    mode: Omote
    scored-date: "2010-04"
    source: Old score
    added-date: "2020-06-07"
    comments:

  - player: GAN
    score: 569,741,232
    ship: Type B
    mode: Ura
    scored-date: "2016-03"
    source: JHA March 2016
    added-date: "2020-06-21"
    comments:
      - 6L remaining

  - player: SPS
    score: 583,614,753
    ship: Type A
    mode: Ura
    scored-date: "2014-05-27"
    source: Arcadia September 2014 / [https:// Twitter]
    added-date: "2020-06-23"
    comments:
      - 6L 0B remaining
      - 1st loop 285m

links:
  - url: https://example.org/jha/ketsui
    title: JHA Leaderboard
    title-jp: 日本ハイスコア協会
  - url: https://example.org/farm/ketsui
    title: Shmups Forum Hi-Score Topic

translations:
  - property: ship
    value: Type A
    value-en: Tiger Schwert
    value-jp: TYPE-A ティーゲルシュベルト
  - property: ship
    value: Type B
    value-en: Panzer Jäger
    value-jp: TYPE-B パンツァーイェーガー
  - property: mode
    value: Omote
    value-jp: 表2週
  - property: mode
    value: Ura
    value-jp: 裏2週

layout:
    columns:
      - label: Ship
        label-jp: 自機
        template: "{{ ship }}"
        groupSameValues: true

      - label: Loop
        label-jp: 2週種
        template: "{{ mode }}"

      - label: Score
        label-jp: スコア
        template: "{{ score }}"

      - label: Player
        label-jp: プレイヤー
        template: "{{ player }}"
        groupSameValues: true

      - label: Date / Source
        label-jp: 年月日 / 情報元
        template: "{{ scored-date }} / {{ source }}"

      - label: Comment
        label-jp: 備考
        template: "{{ comments|join('; ') }}"

    sort:
        scores:
            ship: [ Type A, Type B ]
            mode: asc
&lt;/nowiki&gt;&lt;/pre&gt;
</pre>

{{Anchor|Global settings-value}}
OUTPUT;
}
