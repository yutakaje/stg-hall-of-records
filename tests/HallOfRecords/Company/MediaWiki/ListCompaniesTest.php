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
use Tests\Helper\Data\CompanyEntries;
use Tests\Helper\Data\CompanyEntry;
use Tests\Helper\Data\GameEntries;

class ListCompaniesTest extends \Tests\TestCase
{
    public function testWithEnLocale(): void
    {
        $locale = $this->locale()->get('en');

        $companies = new CompanyEntries([
            $this->data()->createCompany('Capcom', 'capcom'),
            $this->data()->createCompany('Coreland', 'coreland'),
            $this->data()->createCompany('CAVE', 'cave'),
            $this->data()->createCompany('Atlus', 'atlus'),
        ]);

        foreach ($companies->entries() as $company) {
            $this->addGames($company, strlen($company->name($locale)));
        }

        $this->insertCompanies($companies);

        $this->executeTest('list-companies.output.en', $locale);
    }

    public function testWithJaLocale(): void
    {
        $locale = $this->locale()->get('ja');

        $companies = new CompanyEntries([
            $this->data()->createCompany('彩京', 'さいきょう'),
            $this->data()->createCompany('東亜プラン', 'とあぷらん'),
            $this->data()->createCompany('カプコン', 'かぷこん'),
            $this->data()->createCompany('四ツ羽根', 'よつばね'),
        ]);

        foreach ($companies->entries() as $company) {
            $this->addGames($company, strlen($company->name($locale)));
        }

        $this->insertCompanies($companies);

        $this->executeTest('list-companies.output.ja', $locale);
    }

    public function testFiltering(): void
    {
        $companies = new CompanyEntries([
            $this->data()->createCompany('Coreland', 'coreland'),
            $this->data()->createCompany('東亜プラン', 'とあぷらん'),
            $this->data()->createCompany('カプコン', 'かぷこん'),
            $this->data()->createCompany('Capcom', 'capcom'),
            $this->data()->createCompany('彩京', 'さいきょう'),
            $this->data()->createCompany('Atlus', 'atlus'),
            $this->data()->createCompany('CAVE', 'cave'),
            $this->data()->createCompany('四ツ羽根', 'よつばね'),
        ]);

        foreach ($companies->entries() as $i => $company) {
            $this->addGames($company, $i + 1);
        }

        $this->insertCompanies($companies);

        $this->executeTest(
            'list-companies.output.en.filtered',
            $this->locale()->get('en'),
            'name like ca'
        );
        $this->executeTest(
            'list-companies.output.ja.filtered',
            $this->locale()->get('ja'),
            'name like ん'
        );
    }

    private function executeTest(
        string $expectedOutputFile,
        Locale $locale,
        string $filterValue = ''
    ): void {
        $request = $this->http()->createServerRequest(
            'GET',
            "/{$locale}/companies"
        );

        if ($filterValue !== '') {
            $request = $request->withQueryParams([
                'q' => $filterValue,
            ]);
        }

        $response = $this->app()->handle($request);

        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        self::assertSame(
            $this->createOutput(
                $this->filesystem()->loadFile(
                    __DIR__ . "/list-companies/{$expectedOutputFile}"
                ),
                $locale
            ),
            (string)$response->getBody()
        );
    }

    private function createOutput(string $content, Locale $locale): string
    {
        return $this->mediaWiki()->removePlaceholders(
            $this->locale()->translate(
                $locale,
                $this->mediaWiki()->loadMainTemplate(
                    $content,
                    $locale,
                    '/{locale}/companies'
                )
            )
        );
    }

    private function addGames(CompanyEntry $company, int $numGames): void
    {
        // Adding games ensures that the count functions work as expected.
        // The actual game properties are not important here.
        $games = array_map(
            fn (int $i) => $this->data()->createGame($company, "game{$i}"),
            range(1, $numGames)
        );

        $company->setGames(new GameEntries($games));
    }

    private function insertCompanies(CompanyEntries $companies): void
    {
        foreach ($companies->entries() as $company) {
            $this->data()->insertCompany($company);
            $this->data()->insertGames($company->games()->entries());
        }
    }
}
