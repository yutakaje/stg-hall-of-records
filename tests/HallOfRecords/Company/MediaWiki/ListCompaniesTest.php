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
            $this->data()->createCompany('konami'),
            $this->data()->createCompany('cave'),
            $this->data()->createCompany('raizing'),
        ];
    }

    /**
     * @param CompanyEntry[] $companies
     */
    private function insertCompanies(array $companies): void
    {
        foreach ($companies as $company) {
            $this->data()->insertCompany($company);
        }
    }

    /**
     * @param CompanyEntry[] $companies
     */
    private function createOutput(array $companies, string $locale): string
    {
        return str_replace(
            '{{content|raw}}',
            $this->createCompaniesOutput($companies, $locale),
            $this->mediaWiki()->loadTemplate('Shared', 'basic')
        );
    }

    /**
     * @param CompanyEntry[] $companies
     */
    private function createCompaniesOutput(array $companies, string $locale): string
    {
        return str_replace(
            <<<'HTML'
{% for entry in companies %}
  {{ entry|raw }}
{% endfor %}
HTML,
            implode(PHP_EOL, array_map(
                fn (CompanyEntry $company) => $this->createCompanyOutput(
                    $company,
                    $locale
                ),
                [
                    $companies[1],
                    $companies[0],
                    $companies[2],
                ]
            )),
            $this->mediaWiki()->loadTemplate('Company', 'list-companies/main')
        );
    }

    private function createCompanyOutput(
        CompanyEntry $company,
        string $locale
    ): string {
        return str_replace(
            [
                '{{ company.link }}',
                '{{ company.name }}',
            ],
            [
                "/companies/{$company->id()}",
                $company->name($locale),
            ],
            $this->mediaWiki()->loadTemplate('Company', 'list-companies/entry')
        );
    }
}
