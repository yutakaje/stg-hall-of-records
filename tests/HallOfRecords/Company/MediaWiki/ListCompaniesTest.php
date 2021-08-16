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

class ListCompaniesTest extends \Tests\TestCase
{
    public function testWithEnLocale(): void
    {
        // Index represents expected sort order.
        $companies = [
            1 => $this->data()->createCompany('Capcom', 'capcom'),
            0 => $this->data()->createCompany('Atlus', 'atlus'),
            3 => $this->data()->createCompany('Coreland', 'coreland'),
            2 => $this->data()->createCompany('CAVE', 'cave'),
        ];

        foreach ($companies as $company) {
            $this->addGames($company);
        }

        $this->testWithLocale($companies, $this->locale()->get('en'));
    }

    public function testWithJaLocale(): void
    {
        // Index represents expected sort order.
        $companies = [
            1 => $this->data()->createCompany('彩京', 'さいきょう'),
            0 => $this->data()->createCompany('カプコン', 'かぷこん'),
            3 => $this->data()->createCompany('四ツ羽根', 'よつばね'),
            2 => $this->data()->createCompany('東亜プラン', 'とあぷらん'),
        ];

        foreach ($companies as $company) {
            $this->addGames($company);
        }

        $this->testWithLocale($companies, $this->locale()->get('ja'));
    }

    /**
     * @param CompanyEntry[] $companies
     */
    private function testWithLocale(
        array $companies,
        Locale $locale
    ): void {
        $this->insertCompanies($companies);

        $request = $this->http()->createServerRequest(
            'GET',
            "/{$locale}/companies"
        );

        $response = $this->app()->handle($request);

        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        self::assertSame(
            $this->mediaWiki()->canonicalizeHtml(
                $this->createOutput($companies, $locale)
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
        $numGames = random_int(1, 5);
        for ($i = 0; $i < $numGames; ++$i) {
            $company->addGame(
                $this->data()->createGame($company, "game{$i}")
            );
        }
    }

    /**
     * @param CompanyEntry[] $companies
     */
    private function insertCompanies(array $companies): void
    {
        foreach ($companies as $company) {
            $this->data()->insertCompany($company);
            $this->data()->insertGames($company->games());
        }
    }

    /**
     * @param CompanyEntry[] $companies
     */
    private function createOutput(array $companies, Locale $locale): string
    {
        return $this->mediaWiki()->removePlaceholders(
            $this->locale()->translate(
                $locale,
                $this->mediaWiki()->loadBasicTemplate(
                    $this->createCompaniesOutput(
                        $companies,
                        $locale
                    ),
                    $locale,
                    '/{locale}/companies'
                )
            )
        );
    }

    /**
     * @param CompanyEntry[] $companies
     */
    private function createCompaniesOutput(array $companies, Locale $locale): string
    {
        ksort($companies);

        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Company', 'list-companies/main'),
            [
                '{{ entry|raw }}' => implode(PHP_EOL, array_map(
                    fn (CompanyEntry $company) => $this->createCompanyOutput(
                        $company,
                        $locale
                    ),
                    $companies
                )),
            ]
        );
    }

    private function createCompanyOutput(
        CompanyEntry $company,
        Locale $locale
    ): string {
        $numGames = sizeof($company->games());

        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Company', 'list-companies/company-entry'),
            [
                '{{ company.name }}' => $company->name($locale),
                '{{ company.numGames }}' => $numGames,
                "{{ company.numGames == 1 ? 'game' : 'games' }}" => $numGames === 1 ? 'game' : 'games',
                '{{ links.company }}' => "/{$locale}/companies/{$company->id()}",
            ]
        );
    }
}
