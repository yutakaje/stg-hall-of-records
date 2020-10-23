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
use Stg\HallOfRecords\MediaWikiDatabaseFetcher;
use Stg\HallOfRecords\MediaWikiGenerator;
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

    try {
        if (
            isset($_POST['generate'])
            && isset($_POST['input']) && is_string($_POST['input'])
            && isset($_POST['locales']) && is_string($_POST['locales'])
        ) {
            $input = $_POST['input'];
            $locales = $_POST['locales'];

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
            $input = $container->get(MediaWikiDatabaseFetcher::class)->fetch();
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
    ]);
} catch (\Throwable $error) {
    // Make sure that unexpected errors do not leak to the client.
    $identifier = $errorHandler->logError($error);
    http_response_code(500);
    exit("Unexpected server error (identifier: {$identifier})");
}
