<?php

namespace Vinograd\Path;

use Vinograd\Path\Exception\InvalidPathException;

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
    public function replaceAll(array $searchReplace): void
    {
        foreach ($searchReplace as $search => $replace) {
            $this->replace($search, $replace);
        }
    }

    /**
     * @param string $search
     * @param string $replace
     * @return void
     */
    public function replace(string $search, string $replace): void
    {
        foreach ($this->items as $idx => $part) {
            $this->replaceIn($idx, $search, $replace, $part);
        }
    }

    /**
     * @param int $idx
     * @param string $search
     * @param string $replace
     * @param string $part
     * @return void
     */
    private function replaceIn(int $idx, string $search, string $replace, string $part): void
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

    /**
     * @param string $currentValue
     * @param string $newValue
     */
    public function setBy(string $currentValue, string $newValue): void
    {
        if (!$key = $this->getKey($currentValue)) {
            throw new InvalidPathException(sprintf('You are trying to replace "%s" with "%s", but there is no such item in the path.', $currentValue, $newValue));
        }
        $this->items[$key] = $newValue;
    }

    /**
     * @param string $name
     * @return int|null
     */
    public function getKey(string $name): ?int
    {
        if (!$this->contains($name)) {
            return null;
        }
        return array_search($name, $this->items, true);
    }

    /**
     * @param string $needle
     * @return bool
     */
    public function contains(string $needle): bool
    {
        return in_array($needle, $this->items, true);
    }

    /**
     * @param int $key
     * @return string
     */
    public function get(int $key): string
    {
        $this->assertOfBounds($key,
            ['All elements in the path have an index less than the requested one.',
                'Index cannot be less than 0.']);
        return $this->items[$key];
    }

    /**
     * @param int $key
     * @param string $newValue
     */
    public function set(int $key, string $newValue): void
    {
        $this->assertOfBounds($key,
            ['The change could not be completed. All elements in the path have a lower index.',
                'Index cannot be less than 0.']);
        $this->items[$key] = $newValue;
    }

    /**
     * @param int $key
     * @param array $messages
     */
    protected function assertOfBounds(int $key, array $messages): void
    {
        if (count($this->items) <= $key) {
            throw new InvalidPathException($messages[0]);
        }
        if (0 > $key) {
            throw new InvalidPathException($messages[1]);
        }
    }

    public function setAll(array $items): void
    {
        if (empty($items)) {
            throw new  InvalidPathException('The object cannot be changed, there must be at least one element.');
        }
        $this->items = $items;
    }

}