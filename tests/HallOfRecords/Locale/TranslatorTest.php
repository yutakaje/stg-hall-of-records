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
        self::assertSame(['1周目', '2周目'], $translator->translate(
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
        self::assertSame(['1周目', '2周目'], $translator->translate(
            'comment',
            ['first loop', 'second loop']
        ));
    }

    public function testWithFuzzyMatch(): void
    {
        $globalTranslator = new Translator();
        $globalTranslator->add('source', 'Gamest', 'ゲーメスト');

        $translator = new Translator($globalTranslator);
        $translator->add('source', 'Arcadia July 2011', 'Arcadia 2011年07月号 No．114');
        $translator->addFuzzy('source', 'Arcadia', 'アルカディア');
        $translator->addFuzzy('source', 'August (?<year>[1-9][0-9]{3})', '{{year}}年08月号{{suffix}}');

        self::assertSame(
            'Arcadia 2011年07月号 No．114',
            $translator->translate('source', 'Arcadia July 2011')
        );
        self::assertSame(
            'アルカディア May 2015',
            $translator->translate('source', 'Arcadia May 2015')
        );
        self::assertSame(
            'アルカディア 2012年08月号',
            $translator->translate('source', 'Arcadia August 2012')
        );
        self::assertSame('ゲーメスト', $translator->translate('source', 'Gamest'));
    }
}
