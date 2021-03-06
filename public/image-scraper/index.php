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
use Stg\HallOfRecords\MediaWikiImageScraper;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$rootDir = dirname(dirname(__DIR__));
require "{$rootDir}/vendor/autoload.php";

$errorHandler = new ErrorHandler();

try {
    $builder = new ContainerBuilder();
    $builder->addDefinitions("{$rootDir}/app/media-wiki.php");
    $container = $builder->build();

    $errorHandler->registerLogger($container->get(LoggerInterface::class));

    $runtimeLimit = [
        'value' => 30.0,
        'min' => 1.0,
        'max' => 100.0,
    ];
    $errorMessage = '';
    $messages = [];
    $elapsedTime = 0;

    try {
        if (isset($_POST['scrap'])) {
            if (
                isset($_POST['runtimeLimit'])
                && (string)(float)$_POST['runtimeLimit'] === $_POST['runtimeLimit']
            ) {
                $runtimeLimit['value'] = max(
                    $runtimeLimit['min'],
                    min(
                        $runtimeLimit['max'],
                        (float)$_POST['runtimeLimit']
                    )
                );
            }

            $scraper = $container->get(MediaWikiImageScraper::class);
            $scraper->scrap($container->get('save-path'), $runtimeLimit['value']);

            $messages = $scraper->getMessages();
            $elapsedTime = $scraper->getElapsedTime();
        }
    } catch (StgException $exception) {
        $errorMessage = $exception->getMessage();
    }

    $twig = new Environment(
        new FilesystemLoader(__DIR__ . '/templates')
    );
    echo $twig->render('media-wiki-image-scraper.tpl', [
        'runtimeLimit' => $runtimeLimit,
        'error' => $errorMessage,
        'messages' => $messages,
        'saveUrl' => $container->get('save-url'),
        'elapsedTime' => $elapsedTime,
    ]);
} catch (\Throwable $error) {
    // Make sure that unexpected errors do not leak to the client.
    $identifier = $errorHandler->logError($error);
    http_response_code(500);
    exit("Unexpected server error (identifier: {$identifier})");
}
