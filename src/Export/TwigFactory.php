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

namespace Stg\HallOfRecords\Export;

use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\TwigFilter;

final class TwigFactory
{
    private Formatter $formatter;

    public function __construct()
    {
        $this->formatter = new Formatter();
    }

    public function registerFormatter(Formatter $formatter): void
    {
        $this->formatter = $formatter;
    }

    /**
     * @param array<string,string> $templates
     */
    public function create(array $templates): Environment
    {
        $twig = new Environment(
            new ArrayLoader($templates)
        );
        $this->addFilters($twig);
        return $twig;
    }

    private function addFilters(Environment $twig): void
    {
        $twig->addFilter(new TwigFilter(
            'formatDate',
            fn (string $date) => $this->formatter->formatDate($date)
        ));
    }
}
