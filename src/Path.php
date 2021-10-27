<?php

namespace Vinograd\Path;

/**
 * Class Path
 *
 * @package Vinograd\Path
 */
class Path extends AbstractPath
{

    /** @var string */
    protected string $name;

    /**
     * @param string $source
     */
    protected function parse(string $source)
    {
        $this->items = explode(DIRECTORY_SEPARATOR, $source);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function dirname(): string
    {
        return dirname($this->source);
    }

    /**
     * @inheritDoc
     */
    public function setSource(string $source): void
    {
        $this->source = $source;
        $this->source = rtrim($source, $this->getSeparator());
        $this->name = basename($this->source);
        $this->parse($this->source);
    }

    /**
     *
     */
    public function updateSource(): void
    {
        $this->source = implode(DIRECTORY_SEPARATOR, $this->items);
    }

    /**
     * @param array $searchReplace
     */
    public function replaceAll(array $searchReplace)
    {
        foreach ($searchReplace as $search => $replace) {
            $this->replace($search, $replace);
        }
    }

    /**
     * @param $search
     * @param $replace
     */
    public function replace($search, $replace): void
    {
        foreach ($this->items as $idx => $part) {
            $this->replaceIn($idx, $search, $replace, $part);
        }
    }

    /**
     * @param $idx
     * @param $search
     * @param $replace
     * @param $part
     */
    private function replaceIn($idx, $search, $replace, $part)
    {
        $this->items[$idx] = str_replace($search, $replace, $part);
    }

    /**
     * @inheritDoc
     */
    public function reset(): static
    {
        $this->source = '';
        $this->name = '';
        $this->items = [];
        return $this;
    }

    /**
     * @return string
     */
    public function getSeparator(): string
    {
        return DIRECTORY_SEPARATOR;
    }
}