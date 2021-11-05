<?php

namespace Vinograd\Path;

class DefaultUrlPathStrategy implements UrlPathStrategy
{
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
}