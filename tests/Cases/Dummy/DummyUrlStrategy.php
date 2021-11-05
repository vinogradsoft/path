<?php

namespace Test\Cases\Dummy;

use Vinograd\Path\UrlStrategy;
use Vinograd\Path\Url;
use Vinograd\Path\UrlPath;
use Vinograd\Path\UrlQuery;

class DummyUrlStrategy implements UrlStrategy
{

    public function updateRelativeUrl(
        array     $items,
        Url       $url,
        string    $pathString,
        string    $queryString,
        ?UrlPath  $path = null,
        ?UrlQuery $query = null,
        ?string   $suffix = null
    ): string
    {
        return '';
    }

    public function updateQuery(array $items): string
    {
        return rawurldecode(http_build_query($items, "", "&", PHP_QUERY_RFC1738));
    }

    public function updatePath(array $items, ?string $suffix = null): string
    {
        return implode('/', $items);
    }

    public function updateAuthority(array $items, Url $url, bool $idn = false): string
    {
        return '';
    }

    public function updateBaseUrl(array $items, Url $url, string $authority, bool $idn = false): string
    {
        return '';
    }

    public function updateAbsoluteUrl(
        array   $items,
        Url     $url,
        string  $relativeUrl,
        string  $baseUrl,
        bool    $hasPath,
        bool    $idn = false,
        ?string $suffix = null
    ): string
    {
        return '';
    }
}