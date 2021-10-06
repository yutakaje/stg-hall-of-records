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

namespace Tests\HallOfRecords\Company\MediaWiki;

use Fig\Http\Message\StatusCodeInterface;
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;
use Tests\Helper\Data\CompanyEntry;
use Tests\Helper\Data\GameEntries;

class ViewCompanyTest extends \Tests\TestCase
{
    public function testWithEnLocale(): void
    {
        $company = $this->data()->createCompany('Capcom', 'capcom');
        $company->setGames(new GameEntries([
            $this->data()->createGame(
                $company,
                'Aka to Blue Type-R',
                'aka to blue type-r'
            ),
            $this->data()->createGame(
                $company,
                'Akai Katana',
                'akai katana'
            ),
            $this->data()->createGame(
                $company,
                'ASO: Armored Scrum Object / Alpha Mission',
                'aso: armored scrum object / alpha mission'
            ),
            $this->data()->createGame(
                $company,
                'Asuka & Asuka',
                'asuka & asuka'
            ),
        ]));

        $this->insertCompany($company);

        $this->executeTest(
            'view-company.output.en',
            $this->locale()->get('en'),
            $company->id()
        );
    }

    public function testWithJaLocale(): void
    {
        $company = $this->data()->createCompany('彩京', 'さいきょう');
        $company->setGames(new GameEntries([
            $this->data()->createGame(
                $company,
                'エスプレイド',
                'えすぷれいど'
            ),
            $this->data()->createGame(
                $company,
                'ケツイ〜絆地獄たち〜',
                'けついきずなじごくたち'
            ),
            $this->data()->createGame(
                $company,
                '出たな!ツインビー',
                'でたな!ついんびー',
            ),
            $this->data()->createGame(
                $company,
                'バトルガレッガ',
                'ばとるがれっが'
            ),
        ]));

        $this->insertCompany($company);

        $this->executeTest(
            'view-company.output.ja',
            $this->locale()->get('ja'),
            $company->id()
        );
    }

    private function executeTest(
        string $expectedOutputFile,
        Locale $locale,
        int $companyId
    ): void {
        $request = $this->http()->createServerRequest(
            'GET',
            "/{$locale}/companies/{$companyId}"
        );

        $response = $this->app()->handle($request);

        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        self::assertSame(
            $this->createOutput(
                $this->filesystem()->loadFile(
                    __DIR__ . "/view-company/{$expectedOutputFile}"
                ),
                $locale,
                $companyId
            ),
            (string)$response->getBody()
        );
    }

    private function createOutput(
        string $content,
        Locale $locale,
        int $companyId
    ): string {
        return $this->mediaWiki()->removePlaceholders(
            $this->locale()->translate(
                $locale,
                $this->mediaWiki()->loadMainTemplate(
                    $content,
                    $locale,
                    "/{locale}/companies/{$companyId}"
                )
            )
        );
    }

    private function insertCompany(CompanyEntry $company): void
    {
        $this->data()->insertCompany($company);
        $this->data()->insertGames($company->games()->entries());
    }
}
