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

namespace Tests\HallOfRecords\Locale;

use Stg\HallOfRecords\Locale\Translator;

class TranslatorTest extends \Tests\TestCase
{
    public function testWithoutFallbackTranslator(): void
    {
        $translator = new Translator();
        $translator->add('company', 'Cave', 'ケイブ');
        $translator->add(
            'comment',
            ['first loop', 'second loop'],
            ['1周目', '2周目']
        );

        self::assertSame('ケイブ', $translator->translate('company', 'Cave'));
        self::assertSame('Raizing', $translator->translate('company', 'Raizing'));
        self::assertSame('Cave', $translator->translate('player', 'Cave'));
        self::assertSame(['1周目', '2周目'], $translator->translateArray(
            'comment',
            ['first loop', 'second loop']
        ));
    }

    public function testTranslateWithFallbackTranslator(): void
    {
        $globalTranslator = new Translator();
        $globalTranslator->add('company', 'Cave', 'ケイブ');
        $globalTranslator->add(
            'comment',
            ['first loop', 'second loop'],
            ['1周目', '2周目']
        );

        $translator = new Translator($globalTranslator);
        $translator->add('company', 'Raizing', 'ライジング');

        self::assertSame('ケイブ', $translator->translate('company', 'Cave'));
        self::assertSame('ライジング', $translator->translate('company', 'Raizing'));
        self::assertSame('Konami', $translator->translate('company', 'Konami'));
        self::assertSame('Cave', $translator->translate('player', 'Cave'));
        self::assertSame('Raizing', $translator->translate('player', 'Raizing'));
        self::assertSame(['1周目', '2周目'], $translator->translateArray(
            'comment',
            ['first loop', 'second loop']
        ));
    }
}
