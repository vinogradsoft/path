<?php

namespace Vinograd\Path;

class AllUpdateStrategy implements UpdateStrategy
{
    /**
     * @param array $items
     * @param Url $url
     * @return string
     */
    public function updateAuthority(array $items, Url $url): string
    {
        $usrPass = '';
        if (!empty($items[Url::USER]) && !empty($items[Url::PASSWORD])) {
            $usrPass = $items[Url::USER] . ':' . $items[Url::PASSWORD] . '@';
        } elseif (!empty($items[Url::USER]) && empty($items[Url::PASSWORD])) {
            $usrPass = $items[Url::USER] . '@';
        }
        $result = $usrPass;
        $result .= !empty($items[Url::HOST]) ? $items[Url::HOST] : '';
        $result .= !empty($items[Url::PORT]) ? ':' . $items[Url::PORT] : '';
        return $result;
    }

    /**
     * @param array $items
     * @param Url $url
     * @param string $authority
     * @return string
     */
    public function updateBaseUrl(array $items, Url $url, string $authority): string
    {
        $result = !empty($items[Url::SCHEME]) ? $items[Url::SCHEME] . '://' : '';
        $result .= $authority;
        return $result;
    }

    /**
     * @param UrlQuery $query
     * @param int $encodingType
     */
    public function updateQuery(UrlQuery $query, int $encodingType): void
    {
        $query->setEncodingType($encodingType);
        $query->updateSource();
    }

    /**
     * @param Path $path
     */
    public function updatePath(Path $path): void
    {
        $path->updateSource();
    }

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
    ): string
    {
        $result = !empty($pathString) ? $pathString : '';
        $result .= !empty($queryString) ? '?' . $queryString : '';
        $result .= !empty($items[Url::FRAGMENT]) ? '#' . $items[Url::FRAGMENT] : '';
        return $result;
    }

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
    ): string
    {
        return $baseUrl . '/' . ltrim($relativeUrl, '/');
    }

}