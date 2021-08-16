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

namespace Stg\HallOfRecords\Shared\Template;

use Stg\HallOfRecords\Shared\Infrastructure\Locale\TranslatorInterface;
use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

final class Renderer
{
    private TranslatorInterface $translator;
    private ?Environment $twig;
    private ?Locale $locale;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->twig = null;
        $this->locale = null;
    }

    public function withTemplateFiles(string $path): self
    {
        $clone = clone $this;
        $clone->twig = $this->createTwig($path);

        return $clone;
    }

    public function withLocale(Locale $locale): self
    {
        $clone = clone $this;
        $clone->locale = $locale;

        return $clone;
    }

    private function twig(): Environment
    {
        if ($this->twig === null) {
            throw new \LogicException('Twig environment must be set before usage');
        }

        return $this->twig;
    }

    private function locale(): Locale
    {
        if ($this->locale === null) {
            throw new \LogicException('Locale must be set before usage');
        }

        return $this->locale;
    }

    /**
     * @param array<string,mixed> $context
     */
    public function render(string $templateName, array $context = []): string
    {
        $candidates = [
            "{$templateName}.{$this->locale()}.twig",
            "{$templateName}.twig",
        ];

        foreach ($candidates as $candidate) {
            if ($this->twig()->getLoader()->exists($candidate)) {
                return $this->twig()->render($candidate, $context + [
                    'locale' => $this->locale(),
                ]);
            }
        }

        throw new \InvalidArgumentException(
            "Template does not exist: `{$templateName}`"
        );
    }

    private function createTwig(string $path): Environment
    {
        $env = new Environment(
            new FilesystemLoader($path)
        );

        $env->addFilter(new TwigFilter(
            'trans',
            fn ($id) => $this->translator->trans($this->locale(), $id)
        ));

        return $env;
    }
}
