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

namespace Stg\HallOfRecords\Shared\Application\Query;

use Psr\Http\Message\ServerRequestInterface;
use Stg\HallOfRecords\Shared\Infrastructure\Locale\LocaleNegotiator;

final class ListQueryCreator
{
    private LocaleNegotiator $localeNegotiator;

    public function __construct(LocaleNegotiator $localeNegotiator)
    {
        $this->localeNegotiator = $localeNegotiator;
    }

    public function create(ServerRequestInterface $request): ListQuery
    {
        $params = $request->getQueryParams();

        return new ListQuery(
            $this->localeNegotiator->negotiate($request),
            new Filter($params['q'] ?? '')
        );
    }
}
