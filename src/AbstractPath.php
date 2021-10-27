<?php

namespace Vinograd\Path;

use Vinograd\Path\Exception\InvalidPathException;

abstract class AbstractPath implements \Stringable
{
    /** @var string */
    protected string $source;

    /** @var array */
    protected array $items;

    /**
     * Path constructor.
     * @param string $source
     */
    public function __construct(string $source)
    {
        $this->setSource($source);
    }

    /**
     * @param string $source
     */
    abstract protected function parse(string $source);

    /**
     * @return string
     */
    abstract public function getSeparator(): string;

    /**
     *
     */
    abstract public function updateSource(): void;

    /**
     * @return $this
     */
    abstract public function reset(): static;

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @param string $source
     */
    abstract public function setSource(string $source): void;

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

    /**
     * @return array
     */
    public function getAll(): array
    {
        return $this->items;
    }

    /**
     * @param array $items
     */
    public function setAll(array $items): void
    {
        if (empty($items)) {
            throw new  InvalidPathException('The path cannot be changed, there must be at least one element.');
        }
        $this->items = $items;
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
     * @return string
     */
    public function __toString(): string
    {
        return $this->source;
    }
}