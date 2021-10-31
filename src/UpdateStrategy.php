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
     * @param string $queryString
     * @param UrlPath|null $path
     * @param UrlQuery|null $query
     * @return string
     */
    public function updateRelativeUrl(
        array     $items,
        Url       $url,
        string    $pathString,
        string    $queryString,
        ?UrlPath  $path = null,
        ?UrlQuery $query = null
    ): string;

    /**
     * @param array $items
     * @return string
     */
    public function updateQuery(array $items): string;

    /**
     * @param array $items
     * @return string
     */
    public function updatePath(array $items): string;

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