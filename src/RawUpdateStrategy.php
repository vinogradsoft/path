<?php

namespace Vinograd\Path;

class RawUpdateStrategy implements UpdateStrategy
{
    /**
     * @inheritDoc
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
        $result .= !empty($items[Url::HOST]) ? $this->idnToUtf8($items[Url::HOST]) : '';
        $result .= !empty($items[Url::PORT]) ? ':' . $items[Url::PORT] : '';
        return $result;
    }

    /**
     * @param string $host
     * @return string
     */
    protected function idnToUtf8(string $host): string
    {
        if (!str_contains($host, '--')) {
            return $host;
        }
        return idn_to_utf8($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46) ?: $host;
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
        $source = http_build_query($items, '', '&', PHP_QUERY_RFC3986);
        return preg_replace('/\s/', '+', rawurldecode($source));
    }

    /**
     * @inheritDoc
     */
    public function updatePath(array $items): string
    {
        return preg_replace('/\s/', '+', implode('/', $items));
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
        $result .= !empty($items[Url::FRAGMENT]) ? '#' . preg_replace('/\s/', '+', $items[Url::FRAGMENT]) : '';
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