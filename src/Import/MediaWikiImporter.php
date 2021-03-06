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

namespace Stg\HallOfRecords\Import;

use Stg\HallOfRecords\Data\Game\Game;
use Stg\HallOfRecords\Data\Game\GameRepositoryInterface;
use Stg\HallOfRecords\Data\Score\Score;
use Stg\HallOfRecords\Data\Score\ScoreRepositoryInterface;
use Stg\HallOfRecords\Data\Setting\GameSetting;
use Stg\HallOfRecords\Data\Setting\GlobalSetting;
use Stg\HallOfRecords\Data\Setting\SettingRepositoryInterface;
use Stg\HallOfRecords\Error\StgException;
use Stg\HallOfRecords\Import\MediaWiki\ParsedProperties;
use Stg\HallOfRecords\Import\MediaWiki\YamlExtractor;
use Stg\HallOfRecords\Import\MediaWiki\YamlParser;
use Stg\HallOfRecords\Locale\Translator;

final class MediaWikiImporter
{
    private YamlExtractor $extractor;
    private YamlParser $parser;
    private SettingRepositoryInterface $settings;
    private GameRepositoryInterface $games;
    private ScoreRepositoryInterface $scores;
    private int $nextGameId;
    private int $nextScoreId;

    public function __construct(
        YamlExtractor $extractor,
        YamlParser $parser,
        SettingRepositoryInterface $settings,
        GameRepositoryInterface $games,
        ScoreRepositoryInterface $scores
    ) {
        $this->extractor = $extractor;
        $this->parser = $parser;
        $this->settings = $settings;
        $this->games = $games;
        $this->scores = $scores;
        $this->nextGameId = 1;
        $this->nextScoreId = 1;
    }

    public function import(string $input, string $locale): ParsedProperties
    {
        $parsedData = $this->parse($input);

        // Data gets imported into the repositories according to the
        // locale specified, thus they must be reset first.
        $this->clearRepositories();
        $this->populateRepositories($parsedData, $locale);

        return $parsedData;
    }

    private function parse(string $input): ParsedProperties
    {
        return $this->parseYaml(
            $this->extractYaml($input)
        );
    }

    /**
     * @return array[]
     */
    private function extractYaml(string $input): array
    {
        $extractor = new YamlExtractor();
        return $extractor->extract($input);
    }

    /**
     * @param array<string,mixed>[] $sections
     */
    private function parseYaml(array $sections): ParsedProperties
    {
        $parser = new YamlParser();
        return $parser->parse($sections);
    }

    private function populateRepositories(
        ParsedProperties $parsedData,
        string $locale
    ): void {
        $translator = $this->addSettingsToRepository(
            $parsedData->get('global-properties'),
            $locale
        );

        foreach ($parsedData->get('games', []) as $game) {
            $this->addGameToRepository($game, $locale, $translator);
        }
    }

    private function addSettingsToRepository(
        ParsedProperties $settings,
        string $locale
    ): Translator {
        $translator = $this->createTranslator($settings, $locale);

        foreach ($settings->all() as $name => $value) {
            if ($name === 'layout') {
                $value = $this->translateLayout($value, $locale, $translator);
            } else {
                $value = $this->translateProperties($value, $locale, $translator);
            }

            $this->settings->add(new GlobalSetting(
                $name,
                $value
            ));
        }

        return $translator;
    }

    private function addGameToRepository(
        ParsedProperties $game,
        string $locale,
        Translator $translator
    ): void {
        $gameId = $this->nextGameId++;
        $translator = $this->createTranslator($game, $locale, $translator);

        $this->games->add(new Game(
            $gameId,
            $this->getGameProperties($game, $locale, $translator)
        ));

        foreach ($game->get('scores', []) as $score) {
            $this->addScoreToRepository(
                $gameId,
                $score,
                $locale,
                $translator
            );
        }

        $layout = $game->get('layout');
        if ($layout !== null) {
            $this->settings->add(new GameSetting(
                $gameId,
                'layout',
                $this->translateLayout($layout, $locale, $translator)
            ));
        }
    }

    /**
     * @return array<string,mixed>
     */
    private function getGameProperties(
        ParsedProperties $game,
        string $locale,
        Translator $translator
    ): array {
        $properties = $game->all();

        // Scores and layout are parsed separately.
        unset(
            $properties['scores'],
            $properties['layout']
        );

        return $this->translateProperties(
            new ParsedProperties($properties),
            $locale,
            $translator
        );
    }

    private function addScoreToRepository(
        int $gameId,
        ParsedProperties $score,
        string $locale,
        Translator $translator
    ): void {
        $scoreId = $this->nextScoreId++;
        $translator = $this->createTranslator($score, $locale, $translator);

        $this->scores->add(new Score(
            $scoreId,
            $gameId,
            $this->getScoreProperties($score, $locale, $translator)
        ));
    }

    /**
     * @return array<string,mixed>
     */
    private function getScoreProperties(
        ParsedProperties $score,
        string $locale,
        Translator $translator
    ): array {
        return $this->translateProperties($score, $locale, $translator);
    }

    private function clearRepositories(): void
    {
        $this->settings->clear();
        $this->games->clear();
        $this->scores->clear();
    }

    /**
     * @return array<string,mixed>
     */
    private function translateLayout(
        ParsedProperties $layout,
        string $locale,
        Translator $translator
    ): array {
        $properties = $layout->all();

        $columns = $properties['columns'] ?? null;
        $sort = $properties['sort'] ?? null;

        unset(
            $properties['columns'],
            $properties['sort']
        );

        $translated = $this->translateProperties(
            new ParsedProperties($properties),
            $locale,
            $translator
        );


        if ($columns !== null) {
            $translated['columns'] = array_map(
                fn (ParsedProperties $column) => $this->translateLayoutColumn(
                    $column,
                    $locale,
                    $translator
                ),
                $columns
            );
        }

        if ($sort !== null) {
            $translated['sort'] = array_map(
                fn (array $entry) => $this->translateLayoutSort(
                    $entry,
                    $locale,
                    $translator
                ),
                $sort
            );
        }

        return $translated;
    }

    /**
     * @return array<string,mixed>
     */
    private function translateLayoutColumn(
        ParsedProperties $column,
        string $locale,
        Translator $translator
    ): array {
        return $this->translateProperties($column, $locale, $translator);
    }

    /**
     * @param array<string,mixed> $sort
     * @return array<string,mixed>
     */
    private function translateLayoutSort(
        array $sort,
        string $locale,
        Translator $translator
    ): array {
        foreach ($sort as $propertyName => $order) {
            if (is_array($order)) {
                $sort[$propertyName] = array_map(
                    fn (string $value) => $translator->translate($propertyName, $value),
                    array_filter($order, fn ($value) => is_string($value))
                );
            }
        }

        return $sort;
    }

    private function createTranslator(
        ParsedProperties $properties,
        string $locale,
        ?Translator $fallbackTranslator = null
    ): Translator {
        return $this->parseTranslatedProperties(
            $properties,
            $locale,
            $this->parseTranslations(
                $properties,
                $locale,
                $fallbackTranslator
            )
        );
    }

    private function parseTranslations(
        ParsedProperties $properties,
        string $locale,
        ?Translator $fallbackTranslator
    ): Translator {
        return array_reduce(
            array_filter(
                $properties->get('translations', []),
                fn ($entry) => $entry instanceof ParsedProperties
                    && $entry->get('property') !== null
                    && $entry->get('value') !== null
                    && $entry->get("value-{$locale}") !== null
            ),
            function (Translator $translator, ParsedProperties $entry) use ($locale): Translator {
                if ($entry->get('fuzzy-match') === true) {
                    if (
                        !is_string($entry->get('value'))
                        || !is_string($entry->get("value-{$locale}"))
                    ) {
                        throw new StgException(
                            'Fuzzy matching is only available for strings'
                        );
                    }

                    return $translator->addFuzzy(
                        $entry->get('property'),
                        $entry->get('value'),
                        $entry->get("value-{$locale}")
                    );
                } else {
                    return $translator->add(
                        $entry->get('property'),
                        $entry->get('value'),
                        $entry->get("value-{$locale}")
                    );
                }
            },
            new Translator($fallbackTranslator)
        );
    }

    private function parseTranslatedProperties(
        ParsedProperties $properties,
        string $locale,
        Translator $fallbackTranslator
    ): Translator {
        return array_reduce(
            array_filter(
                array_keys($properties->all()),
                fn ($name) => is_string($name)
                    && $properties->get("{$name}-{$locale}") !== null
            ),
            fn (Translator $translator, string $name) => $translator->add(
                $name,
                $properties->get($name),
                $properties->get("{$name}-{$locale}")
            ),
            new Translator($fallbackTranslator)
        );
    }

    /**
     * @param mixed $properties
     * @return mixed
     */
    private function translateProperties(
        $properties,
        string $locale,
        Translator $translator
    ) {
        if ($properties instanceof ParsedProperties) {
            $translator = $this->createTranslator($properties, $locale, $translator);

            return array_reduce(
                array_keys($properties->all()),
                fn (array $translated, string $name) => array_merge(
                    $translated,
                    [
                        $name => $translator->translate(
                            $name,
                            //
                            $this->translateProperties(
                                $properties->get($name),
                                $locale,
                                $translator
                            ),
                        ),
                    ]
                ),
                []
            );
        } elseif (is_array($properties)) {
            return array_map(
                fn ($entry) => $this->translateProperties($entry, $locale, $translator),
                $properties
            );
        } else {
            return $properties;
        }
    }
}
