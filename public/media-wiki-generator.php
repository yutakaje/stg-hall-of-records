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

use DI\ContainerBuilder;
use Psr\Log\LoggerInterface;
use Stg\HallOfRecords\Error\ErrorHandler;
use Stg\HallOfRecords\Error\StgException;
use Stg\HallOfRecords\MediaWikiDatabaseFilter;
use Stg\HallOfRecords\MediaWikiGenerator;
use Stg\HallOfRecords\MediaWikiPageFetcher;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$rootDir = dirname(__DIR__);
require "{$rootDir}/vendor/autoload.php";

$errorHandler = new ErrorHandler();

try {
    $builder = new ContainerBuilder();
    $builder->addDefinitions("{$rootDir}/app/media-wiki.php");
    $container = $builder->build();

    $errorHandler->registerLogger($container->get(LoggerInterface::class));

    $locales = 'en,jp';
    $input = '';
    $output = [];
    $errorMessage = '';
    $allGames = [];
    $gameFilter = '';

    try {
        if (isset($_POST['input']) && is_string($_POST['input'])) {
            $input = $_POST['input'];
        }
        if (isset($_POST['locales']) && is_string($_POST['locales'])) {
            $locales = $_POST['locales'];
        }
        if (isset($_POST['game-filter']) && is_string($_POST['game-filter'])) {
            $gameFilter = $_POST['game-filter'];
        }

        if (isset($_POST['generate'])) {
            $localeList = array_map(
                fn (string $locale) => trim($locale),
                explode(',', $locales)
            );

            $generator = $container->get(MediaWikiGenerator::class);

            foreach ($localeList as $locale) {
                $output[] = [
                    'locale' => $locale,
                    'output' => $generator->generate($input, $locale),
                ];
            }
        } elseif (isset($_POST['load-from-database'])) {
            $input = $container->get(MediaWikiPageFetcher::class)->fetch('database');
            $allGames = $container->get(MediaWikiDatabaseFilter::class)
              ->extractAllGames($input);
        } elseif (isset($_POST['filter-input']) && $gameFilter != null) {
            $input = $container->get(MediaWikiDatabaseFilter::class)
                ->filter($input, $gameFilter);
        }
    } catch (StgException $exception) {
        $errorMessage = $exception->getMessage();
    }

    $twig = new Environment(
        new FilesystemLoader(__DIR__ . '/templates')
    );
    echo $twig->render('media-wiki-generator.tpl', [
        'locales' => $locales,
        'input' => $input,
        'output' => $output,
        'error' => $errorMessage,
        'allGames' => $allGames,
    ]);
} catch (\Throwable $error) {
    // Make sure that unexpected errors do not leak to the client.
    $identifier = $errorHandler->logError($error);
    http_response_code(500);
    exit("Unexpected server error (identifier: {$identifier})");
}
