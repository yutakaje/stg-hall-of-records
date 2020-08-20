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

namespace Stg\HallOfRecords\Import\MediaWiki;

use Symfony\Component\Yaml\Yaml;

final class YamlExtractor
{
    /**
     * @return array[]
     */
    public function extract(string $input): array
    {
        preg_match_all('@<nowiki>(.*?)</nowiki>@us', $input, $matches);

        return array_map(
            fn (string $yaml) => Yaml::parse($yaml),
            $matches[1]
        );
    }
}
