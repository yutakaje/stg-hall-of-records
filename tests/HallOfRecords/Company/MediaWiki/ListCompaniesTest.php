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
        $companies = new CompanyEntries([
            $this->data()->createCompany('Atlus', 'atlus'),
            $this->data()->createCompany('Capcom', 'capcom'),
            $this->data()->createCompany('CAVE', 'cave'),
            $this->data()->createCompany('Coreland', 'coreland'),
        ]);

        foreach ($companies->entries() as $company) {
            $this->addGames($company);
        }

        $this->insertCompanies($companies);

        $this->executeTest($companies, $this->locale()->get('en'));
    }

    public function testWithJaLocale(): void
    {
        $companies = new CompanyEntries([
            $this->data()->createCompany('カプコン', 'かぷこん'),
            $this->data()->createCompany('彩京', 'さいきょう'),
            $this->data()->createCompany('東亜プラン', 'とあぷらん'),
            $this->data()->createCompany('四ツ羽根', 'よつばね'),
        ]);

        foreach ($companies->entries() as $company) {
            $this->addGames($company);
        }

        $this->insertCompanies($companies);

        $this->executeTest($companies, $this->locale()->get('ja'));
    }

    public function testFiltering(): void
    {
        $companies = new CompanyEntries([
            $this->data()->createCompany('Atlus', 'atlus'),
            $this->data()->createCompany('Capcom', 'capcom'),
            $this->data()->createCompany('CAVE', 'cave'),
            $this->data()->createCompany('Coreland', 'coreland'),
            $this->data()->createCompany('カプコン', 'かぷこん'),
            $this->data()->createCompany('彩京', 'さいきょう'),
            $this->data()->createCompany('東亜プラン', 'とあぷらん'),
            $this->data()->createCompany('四ツ羽根', 'よつばね'),
        ]);

        foreach ($companies->entries() as $company) {
            $this->addGames($company);
        }

        $this->insertCompanies($companies);

        $this->executeTest(
            new CompanyEntries([
                $companies->entryAt(1),
                $companies->entryAt(2),
            ]),
            $this->locale()->get('en'),
            'name like ca'
        );
        $this->executeTest(
            new CompanyEntries([
                $companies->entryAt(4),
                $companies->entryAt(6),
            ]),
            $this->locale()->get('ja'),
            'name like ん'
        );
    }

    private function executeTest(
        CompanyEntries $companies,
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
            $this->mediaWiki()->canonicalizeHtml(
                $this->createOutput($companies, $locale, $filterValue)
            ),
            $this->mediaWiki()->canonicalizeHtml(
                (string)$response->getBody()
            )
        );
    }

    private function addGames(CompanyEntry $company): void
    {
        // Adding games ensures that the count functions work as expected.
        // The actual game properties are not important here.
        $games = array_map(
            fn (int $i) => $this->data()->createGame($company, "game{$i}"),
            range(1, random_int(1, 5))
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

    private function createOutput(
        CompanyEntries $companies,
        Locale $locale,
        string $filterValue
    ): string {
        return $this->mediaWiki()->removePlaceholders(
            $this->locale()->translate(
                $locale,
                $this->mediaWiki()->loadMainTemplate(
                    $this->createCompaniesOutput(
                        $companies,
                        $locale,
                        $filterValue
                    ),
                    $locale,
                    '/{locale}/companies'
                )
            )
        );
    }

    private function createCompaniesOutput(
        CompanyEntries $companies,
        Locale $locale,
        string $filterValue
    ): string {
        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Company', 'list-companies/main'),
            [
                '{{ companies|length }}' => $companies->numEntries(),
                '{{ entry|raw }}' => implode(PHP_EOL, array_map(
                    fn (CompanyEntry $company) => $this->createCompanyOutput(
                        $company,
                        $locale
                    ),
                    $companies->sorted()
                )),
                '{{ filterBox|raw }}' => $this->mediaWiki()->loadFilterBoxTemplate(
                    $filterValue,
                    'list-companies'
                ),
            ]
        );
    }

    private function createCompanyOutput(
        CompanyEntry $company,
        Locale $locale
    ): string {
        $numGames = $company->games()->numEntries();

        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Company', 'list-companies/company-entry'),
            [
                '{{ company.name }}' => $company->name($locale),
                '{{ company.numGames }}' => $numGames,
                "{'%count%': company.numGames}" => "{'%count%': {$numGames}}",
                '{{ links.company }}' => "/{$locale}/companies/{$company->id()}",
            ]
        );
    }
}
