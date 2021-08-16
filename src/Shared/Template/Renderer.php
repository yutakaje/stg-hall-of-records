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

use Stg\HallOfRecords\Shared\Infrastructure\Type\Locale;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

final class Renderer
{
    private Environment $twig;
    private ?Locale $locale;

    private function __construct(LoaderInterface $loader)
    {
        $this->twig = new Environment($loader);
        $this->locale = null;
    }

    public static function createWithFiles(string $path): self
    {
        return new self(
            new FilesystemLoader($path)
        );
    }

    public function withLocale(Locale $locale): self
    {
        $clone = clone $this;
        $clone->locale = $locale;

        return $clone;
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
            if ($this->twig->getLoader()->exists($candidate)) {
                return $this->twig->render($candidate, $context + [
                    'locale' => $this->locale(),
                ]);
            }
        }

        throw new \InvalidArgumentException(
            "Template does not exist: `{$templateName}`"
        );
    }
}
