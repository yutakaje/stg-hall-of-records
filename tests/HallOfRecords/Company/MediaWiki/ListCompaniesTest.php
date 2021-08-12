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
use Psr\Http\Message\ServerRequestInterface;
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;
use Tests\Helper\Data\CompanyEntry;

class ListCompaniesTest extends \Tests\TestCase
{
    public function testWithDefaultLocale(): void
    {
        $locale = $this->locale()->default();

        $request = $this->http()->createServerRequest('GET', "/{$locale}/companies");

        $this->testWithLocale($request, $locale);
    }

    public function testWithRandomLocale(): void
    {
        $locale = $this->locale()->random();

        $request = $this->http()->createServerRequest('GET', "/{$locale}/companies")
            ->withHeader('Accept-Language', $locale->value());

        $this->testWithLocale($request, $locale);
    }

    private function testWithLocale(
        ServerRequestInterface $request,
        Locale $locale
    ): void {
        $companies = $this->createCompanies();

        $this->insertCompanies($companies);

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

    /**
     * @return CompanyEntry[]
     */
    private function createCompanies(): array
    {
        // Index represents expected sort order.
        return [
            1 => $this->createCompany('彩京', 'さいきょう'),
            0 => $this->createCompany('カプコン', 'かぷこん'),
            3 => $this->createCompany('四ツ羽根', 'よつばね'),
            2 => $this->createCompany('東亜プラン', 'とあぷらん'),
        ];
    }

    private function createCompany(
        string $name,
        string $translitName = ''
    ): CompanyEntry {
        $company = $this->data()->createCompany($name, $translitName);

        // Add some games for this company to ensure
        // that the count functions work as expected.
        $numGames = random_int(1, 5);
        for ($i = 0; $i < $numGames; ++$i) {
            $company->addGame(
                $this->data()->createGame($company, "game{$i}")
            );
        }

        return $company;
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
        return $this->mediaWiki()->loadBasicTemplate(
            $this->createCompaniesOutput(
                $companies,
                $locale
            ),
            $locale
        );
    }

    /**
     * @param CompanyEntry[] $companies
     */
    private function createCompaniesOutput(array $companies, Locale $locale): string
    {
        ksort($companies);

        return $this->mediaWiki()->removePlaceholders(
            $this->data()->replace(
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
            )
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
