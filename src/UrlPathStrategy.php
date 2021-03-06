<?php

namespace Vinograd\Path;

interface UrlPathStrategy
{

    /**
     * @param array $items
     * @param string|null $suffix
     * @return string
     */
    public function updatePath(array $items, ?string $suffix = null): string;
}