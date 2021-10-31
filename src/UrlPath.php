<?php

namespace Vinograd\Path;

class UrlPath extends AbstractPath
{
    protected UpdateStrategy $strategy;

    /**
     * @param string $source
     * @param UpdateStrategy $strategy
     */
    public function __construct(string $source, UpdateStrategy $strategy)
    {
        $this->strategy = $strategy;
        parent::__construct($source);
    }

    /**
     * @param UpdateStrategy $strategy
     */
    public function setStrategy(UpdateStrategy $strategy): void
    {
        $this->strategy = $strategy;
    }

    /**
     * @inheritDoc
     */
    protected function parse(string $source)
    {
        $this->items = explode('/', $source);
    }

    /**
     * @inheritDoc
     */
    public function getSeparator(): string
    {
        return '/';
    }

    /**
     * @param UpdateStrategy $strategy
     * @return bool
     */
    public function equalsStrategy(UpdateStrategy $strategy): bool
    {
        return $this->strategy === $strategy;
    }

    /**
     * @inheritDoc
     */
    public function updateSource(): void
    {
        $this->source = $this->strategy->updatePath($this->items);
    }

    /**
     * @inheritDoc
     */
    public function reset(): static
    {
        $this->source = '';
        $this->items = [];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setSource(string $source): void
    {
        $this->source = $source;
        $this->source = rtrim($source, $this->getSeparator());
        $this->parse($this->source);
    }
}