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
use Tests\Helper\Data\CompanyEntry;

class ListCompaniesTest extends \Tests\TestCase
{
    public function testWithDefaultLocale(): void
    {
        $request = $this->http()->createServerRequest('GET', '/companies');

        $this->testWithLocale($request, $this->locale()->default());
    }

    public function testWithRandomLocale(): void
    {
        $locale = $this->locale()->random();

        $request = $this->http()->createServerRequest('GET', '/companies')
            ->withHeader('Accept-Language', $locale);

        $this->testWithLocale($request, $locale);
    }

    private function testWithLocale(
        ServerRequestInterface $request,
        string $locale
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
        return [
            $this->createCompany('カプコン', 'かぷこん'),
            $this->createCompany('彩京', 'さいきょう'),
            $this->createCompany('東亜プラン', 'とあぷらん'),
            $this->createCompany('四ツ羽根', 'よつばね'),
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
                $this->data()->createGame("game{$i}", $company)
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
    private function createOutput(array $companies, string $locale): string
    {
        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Shared', 'basic'),
            [
                '{{content|raw}}' => $this->createCompaniesOutput(
                    $companies,
                    $locale
                ),
            ]
        );
    }

    /**
     * @param CompanyEntry[] $companies
     */
    private function createCompaniesOutput(array $companies, string $locale): string
    {
        usort($companies, function ($lhs, $rhs) use ($locale): int {
            return $lhs->translitName($locale) <=> $rhs->translitName($locale);
        });

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
        string $locale
    ): string {
        $numGames = sizeof($company->games());

        return $this->data()->replace(
            $this->mediaWiki()->loadTemplate('Company', 'list-companies/company-entry'),
            [
                '{{ company.link }}' => "/companies/{$company->id()}",
                '{{ company.name }}' => $company->name($locale),
                '{{ company.numGames }}' => $numGames,
                "{{ company.numGames == 1 ? 'game' : 'games' }}" => $numGames === 1 ? 'game' : 'games',
            ]
        );
    }
}
