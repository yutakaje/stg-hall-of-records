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

namespace Tests\HallOfRecords\Import;

use Stg\HallOfRecords\Data\Game\Game;
use Stg\HallOfRecords\Data\Game\GameRepositoryInterface;
use Stg\HallOfRecords\Data\Score\Score;
use Stg\HallOfRecords\Data\Score\ScoreRepositoryInterface;
use Stg\HallOfRecords\Data\Setting\Setting;
use Stg\HallOfRecords\Data\Setting\SettingRepositoryInterface;
use Stg\HallOfRecords\Import\MediaWikiImporter;
use Stg\HallOfRecords\Import\MediaWiki\ParsedProperties;
use Stg\HallOfRecords\Import\MediaWiki\YamlExtractor;
use Stg\HallOfRecords\Import\MediaWiki\YamlParser;

class MediaWikiImporterTest extends \Tests\TestCase
{
    public function testImportWithDefaultLocale(): void
    {
        $locale = '';

        $storage = new \stdClass();
        $storage->settings = [];
        $storage->games = [];
        $storage->scores = [];

        $this->createImporter($storage)->import(
            $this->loadFile(__DIR__ . '/importer.input'),
            $locale
        );

        // Assert that repositories have been populated.
        $this->assertStorage($locale, $storage);
    }

    public function testImportWithEnLocale(): void
    {
        $locale = 'en';

        $storage = new \stdClass();
        $storage->settings = [];
        $storage->games = [];
        $storage->scores = [];

        $this->createImporter($storage)->import(
            $this->loadFile(__DIR__ . '/importer.input'),
            $locale
        );

        // Assert that repositories have been populated.
        $this->assertStorage($locale, $storage);
    }

    public function testImportWithJpLocale(): void
    {
        $locale = 'jp';

        $storage = new \stdClass();
        $storage->settings = [];
        $storage->games = [];
        $storage->scores = [];

        $this->createImporter($storage)->import(
            $this->loadFile(__DIR__ . '/importer.input'),
            $locale
        );

        // Assert that repositories have been populated.
        $this->assertStorage($locale, $storage);
    }

    private function assertStorage(string $locale, \stdClass $storage): void
    {
        self::assertEquals(
            $this->expectedSettings($locale),
            array_map(
                fn (Setting $setting) => $this->exportSetting($setting),
                $storage->settings
            )
        );
        self::assertEquals(
            $this->expectedGames($locale),
            array_map(
                fn (Game $game) => $this->exportGame($game),
                $storage->games
            )
        );
        self::assertEquals(
            $this->expectedScores($locale),
            array_map(
                fn (Score $score) => $this->exportScore($score),
                $storage->scores
            )
        );
    }

    /**
     * @return array<string,mixed>
     */
    private function exportSetting(Setting $setting): array
    {
        $value = $setting->value();

        // Ignore properties for other locales when comparing values.
        if (isset($setting->additionalProperties()['gameId'])) {
            if ($setting->name() === 'layout' && isset($value['columns'])) {
                $value['columns'] = array_map(
                    fn (array $column) => $this->removeLocalizedProperties($column),
                    $value['columns']
                );
            }
        }

        return [
            'name' => $setting->name(),
            'value' => $value,
            'properties' => $setting->additionalProperties(),
        ];
    }

    /**
     * @param array<string,mixed> $properties
     * @return array<string,mixed>
     */
    private function removeLocalizedProperties(array $properties): array
    {
        $keys = array_filter(
            array_keys($properties),
            fn (string $name) => !in_array(
                substr($name, -3),
                ['-en', '-jp'],
                true
            )
        );

        /** @var array<string,mixed> */
        return array_combine($keys, array_map(
            fn (string $key) => $properties[$key],
            $keys
        ));
    }

    /**
     * @return array<string,mixed>
     */
    private function exportGame(Game $game): array
    {
        return [
            'id' => $game->id(),
            'name' => $game->property('name'),
            'company' => $game->property('company'),
            'links' => array_map(
                fn (array $link) => $this->removeLocalizedProperties($link),
                $game->property('links')
            ),
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function exportScore(Score $score): array
    {
        return [
            'id' => $score->id(),
            'gameId' => $score->gameId(),
            'player' => $score->property('player'),
            'score' => $score->property('score'),
            'ship' => $score->property('ship'),
            'mode' => $score->property('mode'),
            'weapon' => $score->property('weapon'),
            'scored-date' => $score->property('scored-date'),
            'source' => $score->property('source'),
            'comments' => $score->property('comments'),
        ];
    }

    private function createImporter(\stdClass $storage): MediaWikiImporter
    {
        $settingRepository = $this->createMock(SettingRepositoryInterface::class);
        $settingRepository->method('add')
            ->will(self::returnCallback(
                function (Setting $setting) use ($storage): void {
                    $storage->settings[] = $setting;
                }
            ));

        $gameRepository = $this->createMock(GameRepositoryInterface::class);
        $gameRepository->method('add')
            ->will(self::returnCallback(
                function (Game $game) use ($storage): void {
                    $storage->games[] = $game;
                }
            ));

        $scoreRepository = $this->createMock(ScoreRepositoryInterface::class);
        $scoreRepository->method('add')
            ->will(self::returnCallback(
                function (Score $score) use ($storage): void {
                    $storage->scores[] = $score;
                }
            ));

        return new MediaWikiImporter(
            new YamlExtractor(),
            new YamlParser(),
            $settingRepository,
            $gameRepository,
            $scoreRepository
        );
    }


    /**
     * @return array<string,mixed>[]
     */
    private function expectedSettings(string $locale): array
    {
        $idGenerator = $this->createIdGenerator();
        $gameIds = [
            $this->nextId($idGenerator),
            $this->nextId($idGenerator),
            $this->nextId($idGenerator),
        ];

        $settings = [
            0 => $this->globalSetting('name', 'global'),
            1 => $this->globalSetting('layout', [
                'templates' => $this->templates(),
                'column-order' => [
                    'player',
                    'mode',
                    'ship',
                    'weapon',
                    'score',
                    'scored-date',
                    'source',
                    'comments',
                ],
                'columns' => [
                    'player' => [
                        'label' => 'Player',
                        'label-jp' => 'プレイヤー',
                        'template' => '{{ player }}',
                    ],
                    'ship' => [
                        'label' => 'Ship',
                        'label-jp' => '自機',
                        'template' => '{{ ship }}',
                    ],
                    'mode' => [
                        'label' => 'Mode',
                        'template' => '{{ mode }}',
                    ],
                    'weapon' => [
                        'label' => 'Weapon',
                        'template' => '{{ weapon }}',
                    ],
                    'score' => [
                        'label' => 'Score',
                        'label-jp' => 'スコア',
                        'template' => '{{ score }}',
                    ],
                    'scored-date' => [
                        'label' => 'Scored date',
                        'template' => '{{ scored-date }}',
                    ],
                    'source' => [
                        'label' => 'Source',
                        'label-jp' => '情報元',
                        'template' => '{{ source }}',
                    ],
                    'comments' => [
                        'label' => 'Comment',
                        'label-jp' => '備考',
                        'template' => "{{ comments|join('; ') }}",
                    ],
                ],
            ]),
            2 => $this->globalSetting('translations', [
                [
                    'property' => 'company',
                    'value' => 'Cave',
                    'value-jp' => 'ケイブ',
                ],
            ]),
            3 => $this->gameSetting($gameIds[0], 'layout', [
                'column-order' => [
                    'mode',
                    'ship',
                    'weapon',
                    'score',
                    'player',
                    'scored-date+source',
                    'comments',
                ],
                'columns' => [
                    'ship' => [
                        'label' => 'Character',
                        'groupSameValues' => true,
                    ],
                    'weapon' => [
                        'label' => 'Style',
                    ],
                    'scored-date+source' => [
                        'label' => 'Date / Source',
                        'template' => '{{ scored-date }} / {{ source }}',
                    ],
                ],
                'sort' => [
                    'scores' => [
                        'mode' => [
                            'Original',
                            'Maniac',
                            'Ultra',
                        ],
                        'ship' => [
                            'Palm',
                            'Reco',
                        ],
                        'weapon' => [
                            'Normal',
                            'Abnormal',
                        ],
                        'score' => 'desc',
                    ],
                ],
            ]),
            4 => $this->gameSetting($gameIds[1], 'layout', [
                'column-order' => [
                    'ship',
                    'mode',
                    'score',
                    'player',
                    'scored-date',
                    'source',
                    'comments',
                ],
                'columns' => [
                    'mode' => [
                        'label' => 'Loop',
                    ],
                    'scored-date' => [
                        'label' => 'Scored date',
                    ],
                ],
                'sort' => [
                    'scores' => [
                        'ship' => [
                            'Type A',
                            'Type B',
                        ],
                        'mode' => 'asc',
                        'score' => 'desc',
                    ],
                ],
            ]),
            5 => $this->gameSetting($gameIds[2], 'layout', [
                'templates' => [
                    'game' => $this->fixedGameTemplate(),
                ],
            ]),
        ];

        if ($locale === 'en') {
            $settings[4]['value']['sort']['scores']['ship'] = [
                'Tiger Schwert',
                'Panzer Jäger',
            ];
        } elseif ($locale === 'jp') {
            $settings[1]['value']['columns']['player']['label'] = 'プレイヤー';
            $settings[1]['value']['columns']['ship']['label'] = '自機';
            $settings[1]['value']['columns']['score']['label'] = 'スコア';
            $settings[1]['value']['columns']['source']['label'] = '情報元';
            $settings[1]['value']['columns']['comments']['label'] = '備考';

            $settings[3]['value']['sort']['scores']['mode'] = [
                'オリジナルモード',
                'マニアックモード',
                'ウルトラモード',
            ];
            $settings[3]['value']['sort']['scores']['ship'] = [
                'パルム',
                'レコ',
            ];
            $settings[3]['value']['sort']['scores']['weapon'] = [
                'ノーマル',
                'アブノーマル',
            ];

            $settings[4]['value']['columns']['mode']['label'] = '2周種';
            $settings[4]['value']['columns']['scored-date']['label'] = '年月日';
            $settings[4]['value']['sort']['scores']['ship'] = [
                'TYPE-A ティーゲルシュベルト',
                'TYPE-B パンツァーイェーガー',
            ];
        }

        return $settings;
    }

    /**
     * @param mixed $value
     * @return array<string,mixed>
     */
    private function globalSetting(string $name, $value): array
    {
        return [
            'name' => $name,
            'value' => $value,
            'properties' => [],
        ];
    }

    /**
     * @param mixed $value
     * @return array<string,mixed>
     */
    private function gameSetting(int $gameId, string $name, $value): array
    {
        return [
            'name' => $name,
            'value' => $value,
            'properties' => [
                'gameId' => $gameId,
            ],
        ];
    }

    /**
     * @return array<string,mixed>[]
     */
    private function expectedGames(string $locale): array
    {
        $idGenerator = $this->createIdGenerator();

        $games = [
            0 => [
                'id' => $this->nextId($idGenerator),
                'name' => 'Mushihimesama Futari 1.5',
                'company' => 'Cave',
                'links' => [
                    [
                        'url' => 'https://example.org/jha/futari',
                        'title' => 'JHA Leaderboard',
                    ],
                    [
                        'url' => 'https://example.org/farm/futari',
                        'title' => 'Shmups Forum Hi-Score Topic',
                    ],
                ],
            ],
            1 => [
                'id' => $this->nextId($idGenerator),
                'name' => 'Ketsui: Kizuna Jigoku Tachi',
                'company' => 'Cave',
                'links' => [
                    [
                        'url' => 'https://example.org/jha/ketsui',
                        'title' => 'JHA Leaderboard',
                    ],
                    [
                        'url' => 'https://example.org/farm/ketsui',
                        'title' => 'Shmups Forum Hi-Score Topic',
                    ],
                ],
            ],
            2 => [
                'id' => $this->nextId($idGenerator),
                'name' => 'Great Mahou Daisakusen',
                'company' => 'Raizing / 8ing',
                'links' => [],
            ],
        ];

        if ($locale === 'en') {
            // Nothing atm
        } elseif ($locale === 'jp') {
            $games[0]['links'][0]['title'] = '日本ハイスコア協会';
            $games[0]['links'][1]['title'] = 'ザ・ファーム';
            $games[0] = array_merge($games[0], [
                'name' => '虫姫さまふたりVer 1.5',
                'company' => 'ケイブ',
            ]);

            $games[1]['links'][0]['title'] = '日本ハイスコア協会';
            $games[1] = array_merge($games[1], [
                'name' => 'ケツイ ～絆地獄たち～',
                'company' => 'ケイブ',
            ]);
        }

        return $games;
    }

    /**
     * @return array<string,mixed>[]
     */
    private function expectedScores(string $locale): array
    {
        $idGenerator = $this->createIdGenerator();
        $gameIds = [
            $this->nextId($idGenerator),
            $this->nextId($idGenerator),
            $this->nextId($idGenerator),
        ];

        $idGenerator = $this->createIdGenerator();

        $scores = [
            0 => [
                'id' => $this->nextId($idGenerator),
                'gameId' => $gameIds[0],
                'player' => 'ABI',
                'score' => '530,358,660',
                'ship' => 'Palm',
                'mode' => 'Original',
                'weapon' => 'Normal',
                'scored-date' => '2008-01',
                'source' => 'Arcadia January 2008',
                'comments' => null,
            ],
            1 => [
                'id' => $this->nextId($idGenerator),
                'gameId' => $gameIds[0],
                'player' => 'ISO / Niboshi',
                'score' => '518,902,716',
                'ship' => 'Palm',
                'mode' => 'Original',
                'weapon' => 'Abnormal',
                'scored-date' => '2007',
                'source' => 'Superplay DVD',
                'comments' => null,
            ],
            2 => [
                'id' => $this->nextId($idGenerator),
                'gameId' => $gameIds[0],
                'player' => 'ABI',
                'score' => '550,705,999',
                'ship' => 'Reco',
                'mode' => 'Original',
                'weapon' => 'Normal',
                'scored-date' => '2010-02',
                'source' => 'Blog',
                'comments' => [
                    '5L 0B remaining',
                    'After stage 4: 273.7m',
                ],
            ],
            3 => [
                'id' => $this->nextId($idGenerator),
                'gameId' => $gameIds[0],
                'player' => 'ISO / Niboshi',
                'score' => '538,378,364',
                'ship' => 'Reco',
                'mode' => 'Original',
                'weapon' => 'Normal',
                'scored-date' => '2007-10',
                'source' => 'Arcadia October 2007',
                'comments' => null,
            ],
            4 => [
                'id' => $this->nextId($idGenerator),
                'gameId' => $gameIds[0],
                'player' => 'yasu0219',
                'score' => '454,386,226',
                'ship' => 'Reco',
                'mode' => 'Original',
                'weapon' => 'Abnormal',
                'scored-date' => '2009-12-12',
                'source' => 'Xbox rankings',
                'comments' => [
                    'Highest score Xbox360',
                ],
            ],
            5 => [
                'id' => $this->nextId($idGenerator),
                'gameId' => $gameIds[0],
                'player' => 'KTL-NAL',
                'score' => '981,872,827',
                'ship' => 'Palm',
                'mode' => 'Maniac',
                'weapon' => 'Abnormal',
                'scored-date' => '2007-09',
                'source' => 'Superplay DVD',
                'comments' => [
                    '5L 2B remaining',
                    'After stage 4: 693.8m',
                ],
            ],
            6 => [
                'id' => $this->nextId($idGenerator),
                'gameId' => $gameIds[0],
                'player' => 'KTL-NAL',
                'score' => '973,020,065',
                'ship' => 'Palm',
                'mode' => 'Maniac',
                'weapon' => 'Abnormal',
                'scored-date' => '2007-11',
                'source' => 'Arcadia November 2007',
                'comments' => null,
            ],
            7 => [
                'id' => $this->nextId($idGenerator),
                'gameId' => $gameIds[0],
                'player' => 'Clover-TAC',
                'score' => '1,047,258,714',
                'ship' => 'Reco',
                'mode' => 'Maniac',
                'weapon' => 'Normal',
                'scored-date' => '2015-03',
                'source' => 'Arcadia March 2015',
                'comments' => [
                    '5L 2B remaining',
                    'After stage 4: 745.1m',
                ],
            ],
            8 => [
                'id' => $this->nextId($idGenerator),
                'gameId' => $gameIds[0],
                'player' => 'rescue_STG',
                'score' => '2,956,728,306',
                'ship' => 'Palm',
                'mode' => 'Ultra',
                'weapon' => 'Normal',
                'scored-date' => '2017-04-08',
                'source' => 'Xbox rankings',
                'comments' => [
                    'Highest score Xbox360',
                ],
            ],
            9 => [
                'id' => $this->nextId($idGenerator),
                'gameId' => $gameIds[0],
                'player' => 'Dame K.K',
                'score' => '3,999,999,999',
                'ship' => 'Palm',
                'mode' => 'Ultra',
                'weapon' => 'Abnormal',
                'scored-date' => '2008-03',
                'source' => 'Arcadia March 2008',
                'comments' => [
                    '1L 0B remaining',
                    'Highest score Arcade',
                ],
            ],
            10 => [
                'id' => $this->nextId($idGenerator),
                'gameId' => $gameIds[0],
                'player' => 'KGM',
                'score' => '3,999,999,999 [4,263,416,356]',
                'ship' => 'Palm',
                'mode' => 'Ultra',
                'weapon' => 'Abnormal',
                'scored-date' => '2013-07-24',
                'source' => 'Xbox rankings',
                'comments' => [
                    'Highest score Xbox360',
                ],
            ],
            11 => [
                'id' => $this->nextId($idGenerator),
                'gameId' => $gameIds[0],
                'player' => 'fufufu',
                'score' => '3,999,999,999',
                'ship' => 'Reco',
                'mode' => 'Ultra',
                'weapon' => 'Normal',
                'scored-date' => '2009-05-27',
                'source' => 'Arcadia August 2009',
                'comments' => [
                    '0L 0B remaining',
                    'After stage 4: 2.205b',
                ],
            ],
            12 => [
                'id' => $this->nextId($idGenerator),
                'gameId' => $gameIds[0],
                'player' => 'lstze',
                'score' => '3,266,405,598',
                'ship' => 'Reco',
                'mode' => 'Ultra',
                'weapon' => 'Abnormal',
                'scored-date' => '2014?',
                'source' => '',
                'comments' => null,
            ],
            13 => [
                'id' => $this->nextId($idGenerator),
                'gameId' => $gameIds[1],
                'player' => 'SPS',
                'score' => '507,780,433',
                'ship' => 'Type A',
                'mode' => 'Omote',
                'weapon' => '',
                'scored-date' => '2014-08',
                'source' => 'Arcadia August 2014',
                'comments' => null,
            ],
            14 => [
                'id' => $this->nextId($idGenerator),
                'gameId' => $gameIds[1],
                'player' => 'SPS',
                'score' => '583,614,753',
                'ship' => 'Type A',
                'mode' => 'Ura',
                'weapon' => '',
                'scored-date' => '2014-05-27',
                'source' => 'Arcadia September 2014 / [https://twitter.com Twitter]',
                'comments' => [
                    '6L 0B remaining',
                    '1st loop 285m',
                ],
            ],
            15 => [
                'id' => $this->nextId($idGenerator),
                'gameId' => $gameIds[1],
                'player' => 'SPS',
                'score' => '481,402,383',
                'ship' => 'Type B',
                'mode' => 'Omote',
                'weapon' => '',
                'scored-date' => '2014-11',
                'source' => 'Arcadia November 2014',
                'comments' => [
                    '6L 0B remaining',
                    '1st loop 276m',
                ],
            ],
            16 => [
                'id' => $this->nextId($idGenerator),
                'gameId' => $gameIds[1],
                'player' => 'GAN',
                'score' => '569,741,232',
                'ship' => 'Type B',
                'mode' => 'Ura',
                'weapon' => '',
                'scored-date' => '2016-03',
                'source' => 'JHA March 2016',
                'comments' => [
                    '6L remaining',
                ],
            ],
        ];

        if ($locale === 'en') {
            $scores[13] = array_merge($scores[13], [
                'ship' => 'Tiger Schwert',
            ]);
            $scores[14] = array_merge($scores[14], [
                'ship' => 'Tiger Schwert',
            ]);
            $scores[15] = array_merge($scores[15], [
                'ship' => 'Panzer Jäger',
            ]);
            $scores[16] = array_merge($scores[16], [
                'ship' => 'Panzer Jäger',
            ]);
        } elseif ($locale === 'jp') {
            $scores[0] = array_merge($scores[0], [
                'ship' => 'パルム',
                'mode' => 'オリジナルモード',
                'weapon' => 'ノーマル',
            ]);
            $scores[1] = array_merge($scores[1], [
                'player' => 'にぼし',
                'ship' => 'パルム',
                'mode' => 'オリジナルモード',
                'weapon' => 'アブノーマル',
            ]);
            $scores[2] = array_merge($scores[2], [
                'ship' => 'レコ',
                'mode' => 'オリジナルモード',
                'weapon' => 'ノーマル',
            ]);
            $scores[3] = array_merge($scores[3], [
                'ship' => 'レコ',
                'mode' => 'オリジナルモード',
                'weapon' => 'ノーマル',
            ]);
            $scores[4] = array_merge($scores[4], [
                'ship' => 'レコ',
                'mode' => 'オリジナルモード',
                'weapon' => 'アブノーマル',
            ]);
            $scores[5] = array_merge($scores[5], [
                'ship' => 'パルム',
                'mode' => 'マニアックモード',
                'weapon' => 'アブノーマル',
            ]);
            $scores[6] = array_merge($scores[6], [
                'ship' => 'パルム',
                'mode' => 'マニアックモード',
                'weapon' => 'アブノーマル',
            ]);
            $scores[7] = array_merge($scores[7], [
                'ship' => 'レコ',
                'mode' => 'マニアックモード',
                'weapon' => 'ノーマル',
            ]);
            $scores[8] = array_merge($scores[8], [
                'ship' => 'パルム',
                'mode' => 'ウルトラモード',
                'weapon' => 'ノーマル',
            ]);
            $scores[9] = array_merge($scores[9], [
                'ship' => 'パルム',
                'mode' => 'ウルトラモード',
                'weapon' => 'アブノーマル',
            ]);
            $scores[10] = array_merge($scores[10], [
                'ship' => 'パルム',
                'mode' => 'ウルトラモード',
                'weapon' => 'アブノーマル',
            ]);
            $scores[11] = array_merge($scores[11], [
                'ship' => 'レコ',
                'mode' => 'ウルトラモード',
                'weapon' => 'ノーマル',
            ]);
            $scores[12] = array_merge($scores[12], [
                'ship' => 'レコ',
                'mode' => 'ウルトラモード',
                'weapon' => 'アブノーマル',
            ]);
            $scores[13] = array_merge($scores[13], [
                'ship' => 'TYPE-A ティーゲルシュベルト',
                'mode' => '表2周',
            ]);
            $scores[14] = array_merge($scores[14], [
                'ship' => 'TYPE-A ティーゲルシュベルト',
                'mode' => '裏2周',
            ]);
            $scores[15] = array_merge($scores[15], [
                'ship' => 'TYPE-B パンツァーイェーガー',
                'mode' => '表2周',
            ]);
            $scores[16] = array_merge($scores[16], [
                'ship' => 'TYPE-B パンツァーイェーガー',
                'mode' => '裏2周',
            ]);
        }

        return $scores;
    }

    /**
     * @return array<string,string>
     */
    private function templates(): array
    {
        return [
            'games' => <<<'TPL'
{% for game in games %}
{% if game.template %}
{{ game.template|raw }}
{% else %}
{{ include('game') }}
{% endif %}
{% endfor %}

TPL,
            'game' => <<<'TPL'
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

TPL,
        ];
    }

    private function fixedGameTemplate(): string
    {
        return <<<'TPL'
{| class="wikitable" style="text-align: center"
|-
! colspan="6" | [[Great Mahou Daisakusen]]
|-
! Ship !! Score !! Player !! Date / Source !! Comment !! Replay
|-
| rowspan="2" | Birthday
| 83,743,680 || rowspan="2" | Miku || August 2nd, 2020 / [https:// Twitter] || 107 items ||
|-
| 66,693,110 || JHA November 2019 || 107 items ||
|-
| rowspan="2" | Chitta
| 93,664,750 || rowspan="2" | SOF-WTN
| August 8th, 2020 / [https://twitter.com Twitter] || 108 items ||
|-
| 83,195,810 || JHA June 2020 || ||
|-
| rowspan="2" | Gain
| 80,528,610 || Boredom || July 1st, 2020 / [https:// Twitter] || 108 items || [https:// Youtube]
|-
| 31,653,130 || HTL-蕨ガイン見参 || JHA June 2020 || ||
|}

Note: Scoreboard closed after the achievement of the counterstop at 99,999,999.

* [https://example.org/some_link_id JHA Leaderboard]
* [https://example.org/some_other_link Shmups Forum Hi-Score Topic]

TPL;
    }
}
