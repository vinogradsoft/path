<?php

namespace Vinograd\Path;

class DefaultUrlQueryStrategy implements UrlQueryStrategy
{
    /**
     * @inheritDoc
     */
    public function updateQuery(array $items): string
    {
        return http_build_query($items, '', '&', PHP_QUERY_RFC3986);
    }
}