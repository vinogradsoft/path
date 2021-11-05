<?php

namespace Vinograd\Path;

interface UrlStrategy extends UrlQueryStrategy, UrlPathStrategy
{
    /**
     * @param array $items
     * @param Url $url
     * @param bool $idn
     * @return string
     */
    public function updateAuthority(array $items, Url $url, bool $idn = false): string;

    /**
     * @param array $items
     * @param Url $url
     * @param string $authority
     * @param bool $idn
     * @return string
     */
    public function updateBaseUrl(array $items, Url $url, string $authority, bool $idn = false): string;

    /**
     * @param array $items
     * @param Url $url
     * @param string $pathString
     * @param string $queryString
     * @param UrlPath $path
     * @param UrlQuery $query
     * @param string|null $suffix
     * @return string
     */
    public function updateRelativeUrl(
        array    $items,
        Url      $url,
        string   $pathString,
        string   $queryString,
        UrlPath  $path,
        UrlQuery $query,
        ?string $suffix = null
    ): string;

    /**
     * @param array $items
     * @param Url $url
     * @param string $relativeUrl
     * @param string $baseUrl
     * @param bool $hasPath
     * @param bool $idn
     * @param string|null $suffix
     * @return string
     */
    public function updateAbsoluteUrl(
        array  $items,
        Url    $url,
        string $relativeUrl,
        string $baseUrl,
        bool   $hasPath,
        bool   $idn = false,
        ?string $suffix = null
    ): string;

}