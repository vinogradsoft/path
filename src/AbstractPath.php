<?php

namespace Vinograd\Path;

use Vinograd\Path\Exception\InvalidPathException;

abstract class AbstractPath
{
    /** @var string */
    protected $source;

    /** @var array */
    protected $directories;

    /**
     * Path constructor.
     * @param string|null $source
     */
    public function __construct(string $source)
    {
        $this->split($this->source);
    }

    /**
     * @param string $source
     */
    abstract protected function split(string $source);

    /**
     * @return string
     */
    abstract protected function getSeparator(): string;

    /**
     *
     */
    abstract public function updateSource(): void;

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @param int $idx
     * @return string
     */
    public function get(int $idx): string
    {
        $this->assertOfBounds($idx,
            'All elements in the path have an index less than the requested one.',
            'Index cannot be less than 0.');
        return $this->directories[$idx];
    }

    /**
     * @param int $idx
     * @param string $newValue
     */
    public function set(int $idx, string $newValue): void
    {
        $this->assertOfBounds($idx,
            'The change could not be completed. All elements in the path have a lower index.',
            'Index cannot be less than 0.');
        $this->directories[$idx] = $newValue;
    }

    /**
     * @param int $idx
     * @param string $moreCardinalityMessage
     * @param string $negativeMessage
     */
    protected function assertOfBounds(int $idx, string $moreCardinalityMessage, string $negativeMessage)
    {
        if (count($this->directories) <= $idx) {
            throw new InvalidPathException($moreCardinalityMessage);
        }
        if (0 > $idx) {
            throw new InvalidPathException($negativeMessage);
        }
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return $this->directories;
    }

    /**
     * @param array $items
     */
    public function setAll(array $items): void
    {
        if (empty($items)) {
            throw new  InvalidPathException('The path cannot be changed, there must be at least one element.');
        }
        $this->directories = $items;
    }

    /**
     * @param string $currentValue
     * @param string $newValue
     */
    public function setBy(string $currentValue, string $newValue): void
    {
        if (!$idx = $this->getIndex($currentValue)) {
            throw new InvalidPathException(sprintf('You are trying to replace "%s" with "%s", but there is no such item in the path.', $currentValue, $newValue));
        }
        $this->directories[$idx] = $newValue;
    }

    /**
     * @param string $name
     * @return int|null
     */
    public function getIndex(string $name): ?int
    {
        if (!$this->contains($name)) {
            return null;
        }
        return array_search($name, $this->directories, true);
    }

    /**
     * @param string $directoryName
     * @return bool
     */
    public function contains(string $directoryName): bool
    {
        return in_array($directoryName, $this->directories, true);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->source;
    }
}