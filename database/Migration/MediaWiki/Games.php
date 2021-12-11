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

namespace Stg\HallOfRecords\Database\Migration\MediaWiki;

use Psr\Log\LoggerInterface;
use Stg\HallOfRecords\Database\Database;
use Stg\HallOfRecords\Database\Definition\GameRecord;
use Stg\HallOfRecords\Data\Game\Game;
use Stg\HallOfRecords\Data\Game\GameRepositoryInterface;
use Stg\HallOfRecords\Data\Setting\SettingRepositoryInterface;
use Stg\HallOfRecords\Data\Score\Score;
use Stg\HallOfRecords\Data\Score\ScoreRepositoryInterface;
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;

/**
 * @phpstan-import-type Link from GameRecord
 * @phpstan-import-type Links from GameRecord
 * @phpstan-import-type Translation from GameRecord
 * @phpstan-import-type Translations from GameRecord
 * @phpstan-import-type Counterstop from GameRecord
 * @phpstan-import-type Counterstops from GameRecord
 */
final class Games
{
    private Database $database;
    private LoggerInterface $logger;
    private Companies $companies;
    private LayoutProperties $layoutProperties;
    private GameRepositoryInterface $sourceGames;
    private SettingRepositoryInterface $sourceSettings;
    private ScoreRepositoryInterface $sourceScores;
    private bool $checkForUnhandledProperties;
    /** @var GameRecord[] */
    private array $records;

    public function __construct(
        Database $database,
        LoggerInterface $logger,
        Companies $companies,
        LayoutProperties $layoutProperties,
        GameRepositoryInterface $sourceGames,
        SettingRepositoryInterface $sourceSettings,
        ScoreRepositoryInterface $sourceScores,
        bool $checkForUnhandledProperties
    ) {
        $this->database = $database;
        $this->logger = $logger;
        $this->companies = $companies;
        $this->layoutProperties = $layoutProperties;
        $this->sourceGames = $sourceGames;
        $this->sourceSettings = $sourceSettings;
        $this->sourceScores = $sourceScores;
        $this->checkForUnhandledProperties = $checkForUnhandledProperties;
        $this->records = [];
    }

    public function insert(): void
    {
        $this->logger->info('Importing games');

        $start = microtime(true);

        $this->records = $this->createRecords();

        $this->database->games()->insertRecords($this->records);

        $this->logger->info('Games imported', [
            'total' => sizeof($this->records),
            'elapsed' => microtime(true) - $start,
        ]);
    }

    /**
     * @return GameRecord[]
     */
    private function createRecords(): array
    {
        return $this->sourceGames->all()
            ->map(fn (Game $game) => $this->createRecord($game));
    }

    private function createRecord(Game $game): GameRecord
    {
        $this->logger->debug('Creating game', [
            'id' => $game->id(),
            'name' => $game->property('name'),
        ]);

        $properties = new Properties($game->properties());

        $company = $properties->consume('company');
        $names = [
            'en' => $properties->consume('name'),
            'ja' => $properties->consume('name-jp'),
        ];
        $namesSort = [
            'en' => mb_strtolower(
                $properties->consume('name-sort', $names['en'])
            ),
            'ja' => mb_strtolower(
                $properties->consume('name-sort-jp', $names['ja'])
            ),
        ];

        $description = $properties->consume('description', '');
        $descriptions = [
            'en' => $description,
            'ja' => $properties->consume('description-jp', $description),
        ];

        $links = $properties->consume('links', []);

        $translations = $properties->consume('translations', []);

        $counterstops = $properties->consume('counterstop', []);

        $properties->remove('id', 'needs-work');

        if ($this->checkForUnhandledProperties) {
            $properties->assertEmpty();
        }

        return $this->database->games()->createRecord(
            $this->companies->find($company)->id(),
            $names,
            $namesSort,
            $descriptions,
            [
                'en' => $this->createLinks('en', $links),
                'ja' => $this->createLinks('jp', $links),
            ],
            [
                'en' => $this->createTranslations('en', $translations),
                'ja' => $this->createTranslations('jp', $translations),
            ],
            $this->createCategories($game->id()),
            $this->createCounterstops($counterstops)
        );
    }

    public function find(int $id): GameRecord
    {
        foreach ($this->records as $record) {
            if ($record->id() === $id) {
                return $record;
            }
        }

        throw new \InvalidArgumentException(
            "Game with id `{$id}` does not exist."
        );
    }

    /**
     * @return string[]
     */
    private function createCategories(int $gameId): array
    {
        return array_values(array_intersect(
            $this->layoutProperties->find('categories')->value(),
            $this->sourceScores->filterByGame($gameId)->reduce(
                fn (array $properties, Score $score) => array_merge(
                    $properties,
                    array_keys($score->properties())
                ),
                []
            )
        ));
    }

    /**
     * @param array<string,mixed>[] $links
     * @return Links
     */
    private function createLinks(string $locale, array $links): array
    {
        return array_map(
            fn (array $link) => $this->createLink($locale, $link),
            $links
        );
    }

    /**
     * @param array<string,mixed> $link
     * @return Link
     */
    private function createLink(string $locale, array $link): array
    {
        $properties = new Properties($link);

        $url = $properties->consume('url');
        $title = $properties->consume('title');

        if (!$this->checkForUnhandledProperties) {
            $properties->assertEmpty();
        }

        return [
            'url' => $url,
            'title' => $title,
        ];
    }

    /**
     * @param array<string,mixed>[] $translations
     * @return Translations
     */
    private function createTranslations(string $locale, array $translations): array
    {
        return array_map(
            fn (array $translation) => $this->createTranslation($locale, $translation),
            $translations
        );
    }

    /**
     * @param array<string,mixed> $translation
     * @return Translation
     */
    private function createTranslation(string $locale, array $translation): array
    {
        $properties = new Properties($translation);

        $property = $properties->consume('property');
        $value = $properties->consume('value');
        $translation = $properties->consume("value-{$locale}", $value);

        $properties->remove('value-en', 'value-jp');

        if (!$this->checkForUnhandledProperties) {
            $properties->assertEmpty();
        }

        return [
            'property' => $property,
            'value' => $value,
            'translation' => $translation,
        ];
    }

    /**
     * @param array<string,mixed>|array<string,mixed>[] $counterstops
     * @return Counterstops
     */
    private function createCounterstops(array $counterstops): array
    {
        // There may be a single or multiple counterstops.
        if (array_key_exists('score', $counterstops)) {
            return [
                $this->createCounterstop($counterstops),
            ];
        } else {
            return array_map(
                fn ($counterstop) => $this->createCounterstop($counterstop),
                array_values($counterstops)
            );
        }
    }

    /**
     * @param array<string,mixed> $counterstop
     * @return Counterstop
     */
    private function createCounterstop(array $counterstop): array
    {
        $properties = new Properties($counterstop);

        $type = $properties->consume('type');
        $score = $properties->consume('score');

        if (!$this->checkForUnhandledProperties) {
            $properties->assertEmpty();
        }

        return [
            'type' => $type,
            'score' => $score,
        ];
    }
}
