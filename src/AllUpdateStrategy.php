<?php

namespace Vinograd\Path;

class AllUpdateStrategy implements UpdateStrategy
{

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

    public function updateBaseUrl(array $items, Url $url, string $authority): string
    {
        $result = !empty($items[Url::SCHEME]) ? $items[Url::SCHEME] . '://' : '';
        $result .= $authority;
        return $result;
    }

    public function updateQuery(UrlQuery $query, int $encodingType): void
    {
        $query->setEncodingType($encodingType);
        $query->updateSource();
    }

    public function updatePath(Path $path): void
    {
        $path->updateSource();
    }

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