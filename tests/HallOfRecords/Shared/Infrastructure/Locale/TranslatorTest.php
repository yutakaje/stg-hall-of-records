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

namespace Tests\HallOfRecords\Shared\Infrastructure\Locale;

use Stg\HallOfRecords\Shared\Infrastructure\Locale\LocaleDir;
use Stg\HallOfRecords\Shared\Infrastructure\Locale\Locales;
use Stg\HallOfRecords\Shared\Infrastructure\Locale\Translator;
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;

class TranslatorTest extends \Tests\TestCase
{
    public function testWithLocales(): void
    {
        $locales = [
            'en' => new Locale('en'),
            'ja' => new Locale('ja'),
            'kr' => new Locale('kr'),
        ];

        $translator = new Translator($this->localeDir(), new Locales('en', [
            $locales['en'],
            $locales['ja'],
        ]));

        self::assertSame('Companies', $translator->trans($locales['en'], 'labels.companies'));
        self::assertSame('開発会社', $translator->trans($locales['ja'], 'labels.companies'));
        self::assertSame('Companies', $translator->trans($locales['kr'], 'labels.companies'));
    }

    private function localeDir(): LocaleDir
    {
        return new LocaleDir("{$this->filesystem()->rootDir()}/locale");
    }
}
