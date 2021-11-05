<?php

namespace Vinograd\Path;

class DefaultUrlStrategy implements UrlStrategy
{
    /**
     * @inheritDoc
     */
    public function updateAuthority(array $items, Url $url, bool $idn = false): string
    {
        if (empty($items[Url::HOST])) {
            return '';
        }
        $usrPass = '';
        if (!empty($items[Url::USER]) && !empty($items[Url::PASSWORD])) {
            $usrPass = rawurlencode($items[Url::USER]) . ':' . rawurlencode($items[Url::PASSWORD]) . '@';
        } elseif (!empty($items[Url::USER]) && empty($items[Url::PASSWORD])) {
            $usrPass = rawurlencode($items[Url::USER]) . '@';
        }

        $result = $usrPass;
        $result .= $idn ? $this->idnToAscii($items[Url::HOST]) : $items[Url::HOST];
        $result .= !empty($items[Url::PORT]) ? ':' . $items[Url::PORT] : '';
        return $result;
    }

    /**
     * @param string $host
     * @return string
     */
    protected function idnToAscii(string $host): string
    {
        if (str_contains($host, '--')) {
            return $host;
        }
        return idn_to_ascii($host) ?: $host;
    }

    /**
     * @inheritDoc
     */
    public function updateBaseUrl(array $items, Url $url, string $authority, bool $idn = false): string
    {
        if (empty($authority)) {
            return '';
        }
        if (empty($items[Url::SCHEME])) {
            return '';
        }
        return $items[Url::SCHEME] . '://' . $authority;
    }

    /**
     * @inheritDoc
     */
    public function updateQuery(array $items): string
    {
        return http_build_query($items, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * @inheritDoc
     */
    public function updatePath(array $items, ?string $suffix = null): string
    {
        if (empty($items)) {
            return '';
        }
        return $suffix ? implode('/', $items) . $suffix : implode('/', $items);
    }

    /**
     * @inheritDoc
     */
    public function updateRelativeUrl(
        array    $items,
        Url      $url,
        string   $pathString,
        string   $queryString,
        UrlPath  $path,
        UrlQuery $query,
        ?string  $suffix = null
    ): string
    {
        $result = !empty($pathString) ? $pathString : '';
        $result .= !empty($queryString) ? '?' . $queryString : '';
        $result .= !empty($items[Url::FRAGMENT]) ? '#' . $items[Url::FRAGMENT] : '';
        return $result;
    }

    /**
     * @inheritDoc
     */
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
        if(empty($baseUrl)){
            return '';
        }
        return !empty($relativeUrl) ? $baseUrl . '/' . ltrim($relativeUrl, '/') : $baseUrl;
    }

}