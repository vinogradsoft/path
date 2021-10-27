<?php

namespace Vinograd\Path;

interface UpdateStrategy
{
    /**
     * @param array $items
     * @param Url $url
     * @return string
     */
    public function updateAuthority(array $items, Url $url): string;

    /**
     * @param array $items
     * @param Url $url
     * @param string $authority
     * @return string
     */
    public function updateBaseUrl(array $items, Url $url, string $authority): string;

    /**
     * @param array $items
     * @param Url $url
     * @param string $pathString
     * @param Path|null $path
     * @param string $queryString
     * @param UrlQuery|null $query
     * @return string
     */
    public function updateRelativeUrl(
        array     $items,
        Url       $url,
        string    $pathString,
        ?Path     $path = null,
        string    $queryString,
        ?UrlQuery $query = null
    ): string;

    /**
     * @param UrlQuery $query
     * @param int $encodingType PHP_QUERY_RFC1738 | PHP_QUERY_RFC3986
     */
    public function updateQuery(UrlQuery $query, int $encodingType): void;

    /**
     * @param Path $path
     */
    public function updatePath(Path $path): void;

    /**
     * @param array $items
     * @param Url $url
     * @param string $relativeUrl
     * @param string $baseUrl
     * @param bool $hasPath
     * @return string
     */
    public function updateAbsoluteUrl(
        array  $items,
        Url    $url,
        string $relativeUrl,
        string $baseUrl,
        bool   $hasPath
    ): string;

}