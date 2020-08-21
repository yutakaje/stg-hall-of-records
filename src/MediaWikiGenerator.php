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

namespace Stg\HallOfRecords;

use Stg\HallOfRecords\Export\MediaWikiExporter;
use Stg\HallOfRecords\Import\MediaWikiImporter;

final class MediaWikiGenerator
{
    private MediaWikiImporter $importer;
    private MediaWikiExporter $exporter;

    public function __construct(
        MediaWikiImporter $importer,
        MediaWikiExporter $exporter
    ) {
        $this->importer = $importer;
        $this->exporter = $exporter;
    }

    public function generate(string $input, string $locale): string
    {
        $this->import($input, $locale);
        return $this->export();
    }

    private function import(string $input, string $locale): void
    {
        $this->importer->import($input, $locale);
    }

    private function export(): string
    {
        return $this->exporter->export();
    }
}
