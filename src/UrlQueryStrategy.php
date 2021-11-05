<?php

namespace Vinograd\Path;

interface UrlQueryStrategy
{

    /**
     * @param array $items
     * @return string
     */
    public function updateQuery(array $items): string;
}