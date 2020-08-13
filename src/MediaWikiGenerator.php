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

use Stg\HallOfRecords\Database\ParsedDataWriter;
use Stg\HallOfRecords\Export\MediaWikiExporter;
use Stg\HallOfRecords\Import\YamlExtractor;
use Stg\HallOfRecords\Import\YamlParser;

final class MediaWikiGenerator
{
    private YamlExtractor $yamlExtractor;
    private YamlParser $yamlParser;
    private ParsedDataWriter $parsedDataWriter;
    private MediaWikiExporter $exporter;

    public function __construct(
        YamlExtractor $yamlExtractor,
        YamlParser $yamlParser,
        ParsedDataWriter $parsedDataWriter,
        MediaWikiExporter $exporter
    ) {
        $this->yamlExtractor = $yamlExtractor;
        $this->yamlParser = $yamlParser;
        $this->parsedDataWriter = $parsedDataWriter;
        $this->exporter = $exporter;
    }

    public function generate(string $input, string $locale): string
    {
        $parsedData = $this->yamlParser->parse(
            $this->yamlExtractor->extract($input),
            $locale
        );

        $this->parsedDataWriter->write($parsedData);

        return $this->exporter->export();
    }
}
