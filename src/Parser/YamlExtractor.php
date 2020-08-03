<?php

declare(strict_types=1);

/*
 * This file is part of the stg/hall-of-records package.
 *
 * (c) YTK <yutakaje@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Stg\HallOfRecords\Parser;

use Symfony\Component\Yaml\Yaml;

final class YamlExtractor
{
    private string $input;

    public function __construct(string $input)
    {
        $this->input = $input;
    }

    /**
     * @return array[]
     */
    public function extract(): array
    {
        preg_match_all('@<nowiki>(.*?)</nowiki>@us', $this->input, $matches);

        return array_map(
            fn (string $yaml) => Yaml::parse($yaml),
            $matches[1]
        );
    }
}
