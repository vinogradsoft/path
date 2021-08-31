<?php

namespace Vinograd\Path;

use Vinograd\Path\Exception\InvalidPathException;

/**
 * Class Path
 *
 * @package Vinograd\Path
 */
class Path extends AbstractPath
{

    /** @var string */
    protected $name;

    /**
     * Path constructor.
     * @param string|null $source
     */
    public function __construct(string $source)
    {
        $this->source = rtrim($source, $this->getSeparator());
        $this->name = basename($this->source);
        parent::__construct($source);
    }

    /**
     * @param string $source
     */
    protected function split(string $source)
    {
        $this->directories = explode(DIRECTORY_SEPARATOR, $source);
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
     *
     */
    public function updateSource(): void
    {
        $this->source = implode(DIRECTORY_SEPARATOR, $this->directories);
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
        foreach ($this->directories as $idx => $part) {
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
        $this->directories[$idx] = str_replace($search, $replace, $part);
    }

    /**
     * @return string
     */
    protected function getSeparator(): string
    {
        return DIRECTORY_SEPARATOR;
    }
}