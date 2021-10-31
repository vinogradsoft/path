<?php

namespace Vinograd\Path;

class EncodeUpdateStrategy implements UpdateStrategy
{
    /**
     * @inheritDoc
     */
    public function updateAuthority(array $items, Url $url): string
    {
        $usrPass = '';
        if (!empty($items[Url::USER]) && !empty($items[Url::PASSWORD])) {
            $usrPass = rawurlencode($items[Url::USER]) . ':' . rawurlencode($items[Url::PASSWORD]) . '@';
        } elseif (!empty($items[Url::USER]) && empty($items[Url::PASSWORD])) {
            $usrPass = rawurlencode($items[Url::USER]) . '@';
        }

        $result = $usrPass;
        $result .= !empty($items[Url::HOST]) ? $this->idnToAscii($items[Url::HOST]) : '';
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
    public function updateBaseUrl(array $items, Url $url, string $authority): string
    {
        $result = !empty($items[Url::SCHEME]) ? $items[Url::SCHEME] . '://' : '';
        $result .= $authority;
        return $result;
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
    public function updatePath(array $items): string
    {
        return rawurlencode(implode('/', $items));
    }

    /**
     * @inheritDoc
     */
    public function updateRelativeUrl(
        array     $items,
        Url       $url,
        string    $pathString,
        string    $queryString,
        ?UrlPath  $path = null,
        ?UrlQuery $query = null
    ): string
    {
        $result = !empty($pathString) ? $pathString : '';
        $result .= !empty($queryString) ? '?' . $queryString : '';
        $result .= !empty($items[Url::FRAGMENT]) ? '#' . rawurlencode($items[Url::FRAGMENT]) : '';
        return $result;
    }

    /**
     * @inheritDoc
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