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

namespace Stg\HallOfRecords;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use Stg\HallOfRecords\Error\StgException;

final class MediaWikiPageFetcher
{
    private string $url;
    /** @var array<string,string> */
    private array $pageAliases;

    /**
     * @param array<string,string> $pageAliases
     */
    public function __construct(string $url, array $pageAliases)
    {
        $this->url = $url;
        $this->pageAliases = $pageAliases;
    }

    public function fetch(string $page): string
    {
        return $this->fetchContents(
            $this->handleAliases($page)
        );
    }

    public function download(string $page, bool $includeFiles = false): void
    {
        $page = $this->handleAliases($page);

        if ($includeFiles) {
            $this->downloadAsZipFile($page);
        } else {
            $this->downloadAsTextFile($page);
        }
    }

    private function downloadAsTextFile(string $page): void
    {
        $contents = $this->fetch($page);

        $filename = $this->makeFilename($page) . '.txt';
        header('Content-Type: text/plain; charset=UTF-8');
        header('Content-disposition: attachment; filename="' . $filename . '"');
        echo $contents;
        exit(0);
    }

    private function downloadAsZipFile(string $page): void
    {
        $contents = $this->fetch($page);

        $files = $this->fetchFiles(
            $this->extractReferencedFiles($contents)
        );

        $zip = $this->createZipFile($contents, $files);

        $filename = $this->makeFilename($page) . '.zip';
        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename="' . $filename . '"');
        echo file_get_contents($zip);
        exit(0);
    }

    private function fetchContents(string $page): string
    {
        $body = $this->fetchUrl(
            $this->makePageUrl($page)
        );

        $startPos = strpos($body, 'name="wpTextbox1">');
        if ($startPos === false) {
            throw $this->createException(
                'Error calculating start position within wiki contents'
            );
        }
        $startPos += 18;

        $endPos = strpos($body, '</textarea>', $startPos);
        if ($endPos === false) {
            throw $this->createException(
                'Error calculating end position within wiki contents'
            );
        }

        $contents = html_entity_decode(substr($body, $startPos, $endPos - $startPos));

        if ($contents == null) {
            throw $this->createException("Page named `{$page}` does not exist or is empty");
        }

        $redirectPage = $this->getsRedirectedTo($contents);
        if ($redirectPage !== null) {
            return $this->fetchContents($redirectPage);
        }

        return $contents;
    }

    private function fetchUrl(string $url): string
    {
        $httpClient = new HttpClient();
        try {
            $response = $httpClient->request('GET', $url);
        } catch (ClientException $exception) {
            throw $this->createException($exception->getMessage());
        }

        if ($response->getStatusCode() !== 200) {
            throw $this->createException(
                "Error retrieving contents from URL `{$url}`"
            );
        }

        return (string)$response->getBody();
    }

    /**
     * @param string[] $files
     * @return string[]
     */
    private function fetchFiles(array $files): array
    {
        return array_reduce(
            $files,
            fn (array $fetched, string $file) => array_merge($fetched, [
                $file => $this->fetchFile($file),
            ]),
            []
        );
    }

    private function fetchFile(string $file): string
    {
        // Actual file has to be extracted from a HTML page.
        $pageContents = $this->fetchUrl($this->makeFileUrl($file));

        $pattern = '@ id="file"><a href="(.+?)"@u';

        if (preg_match($pattern, $pageContents, $matches) !== 1) {
            throw $this->createException("Unable to extract file named `{$file}`");
        }

        return $this->fetchUrl("{$this->url}{$matches[1]}");
    }

    private function getsRedirectedTo(string $contents): ?string
    {
        // Handle media wiki redirects.
        if (strpos($contents, '#REDIRECT [[') !== 0) {
            return null;
        }

        $pattern = '@#REDIRECT \[\[(.*?)\]\]@u';

        if (preg_match($pattern, $contents, $matches) !== 1) {
            throw $this->createException('Unable to extract redirect url');
        }

        return $matches[1];
    }

    /**
     * @param array<string,string> $files
     */
    private function createZipFile(string $contents, array $files): string
    {
        $name = tempnam(sys_get_temp_dir(), 'zip');

        if ($name === false) {
            throw $this->createException('Unable to create temporary zip file');
        }

        $zip = new \ZipArchive();

        $result = $zip->open($name, \ZipArchive::OVERWRITE);
        if ($result !== true) {
            throw $this->createException(
                "Unable to create zip file, error {$result}"
            );
        }

        $zip->addFromString('page.txt', $contents);

        $zip->addEmptyDir('files');
        foreach ($files as $filename => $fileContents) {
            $zip->addFromString("files/{$filename}", $fileContents);
        }

        $zip->close();

        return $name;
    }

    /**
     * @return string[]
     */
    private function extractReferencedFiles(string $contents): array
    {
        $pattern = '@\[\[File:(.+?)\]\]@u';

        if (preg_match_all($pattern, $contents, $matches) === false) {
            throw $this->createException('Unable to extract referenced files');
        }

        return array_map(
            fn (string $match) => $this->removeModifiersFromFileReference($match),
            $matches[1]
        );
    }

    private function removeModifiersFromFileReference(string $reference): string
    {
        $firstModifierPos = strpos($reference, '|');

        if ($firstModifierPos === false) {
            return $reference;
        }

        return substr($reference, 0, $firstModifierPos);
    }

    private function makePageUrl(string $page): string
    {
        return "{$this->url}/index.php?title={$page}&action=edit";
    }

    private function makeFileUrl(string $file): string
    {
        return "{$this->url}/library/File:{$file}";
    }

    private function makeFilename(string $page): string
    {
        // Not perfect but good enough for now.
        return date('Y-m-d\TH-i_') . str_replace('/', '_', $page);
    }

    private function handleAliases(string $page): string
    {
        return $this->pageAliases[$page] ?? $page;
    }

    private function createException(string $message): StgException
    {
        return new StgException("Error fetching page_input: {$message}");
    }
}
