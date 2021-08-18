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

namespace Stg\HallOfRecords\Shared\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Stg\HallOfRecords\Shared\Infrastructure\Locale\LocaleNegotiator;
use Stg\HallOfRecords\Shared\Template\IndexTemplateInterface;

final class IndexController
{
    private IndexTemplateInterface $template;
    private LocaleNegotiator $localeNegotiator;
    private LoggerInterface $logger;

    public function __construct(
        IndexTemplateInterface $template,
        LocaleNegotiator $localeNegotiator,
        LoggerInterface $logger
    ) {
        $this->template = $template;
        $this->localeNegotiator = $localeNegotiator;
        $this->logger = $logger;
    }

    /**
     * @param QueryParams $args
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args = []
    ): ResponseInterface {
        $this->logger->info('Index viewed.');

        return $this->template->respond(
            $response,
            $this->localeNegotiator->negotiate($request)
        );
    }
}
