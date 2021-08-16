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

namespace Stg\HallOfRecords\Shared\Infrastructure\Locale;

use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator as SymfonyTranslator;

final class Translator implements TranslatorInterface
{
    private SymfonyTranslator $translator;

    public function __construct(LocaleDir $localeDir, Locales $locales)
    {
        $translator = new SymfonyTranslator('');
        $translator->addLoader('yaml', new YamlFileLoader());
        $translator->setFallbackLocales([(string)$locales->default()]);

        foreach ($locales->all() as $locale) {
            $translator->addResource(
                'yaml',
                "{$localeDir->value()}/{$locale}/labels.yaml",
                (string)$locale
            );
        }

        $this->translator = $translator;
    }

    public function trans(Locale $locale, string $id, array $parameters = []): string
    {
        return $this->translator->trans($id, $parameters, null, (string)$locale);
    }
}
