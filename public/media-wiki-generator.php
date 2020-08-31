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
use Stg\HallOfRecords\MediaWikiDatabaseFetcher;
use Stg\HallOfRecords\MediaWikiGenerator;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$rootDir = dirname(__DIR__);
require "{$rootDir}/vendor/autoload.php";

try {
    $builder = new ContainerBuilder();
    $builder->addDefinitions("{$rootDir}/app/media-wiki.php");
    $container = $builder->build();

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
    } catch (\InvalidArgumentException $exception) {
        // @TODO: Should be a GeneratorException or something.
        $errorMessage = $exception->getMessage();
    } catch (\UnexpectedValueException $exception) {
        // @TODO: Should be a FetchException or something.
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
    http_response_code(500);
    // @TODO: Use Monolog or similiar.
    error_log($error->getMessage());
    exit('Unexpected server error');
}
