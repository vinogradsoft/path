<?php

namespace Vinograd\Path;

class UrlQuery extends AbstractPath
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
     * ["query"]=> "name=param&name2=para2m&n[]=f&n[]=f2&n[]=f3"
     * @param string $source
     */
    protected function parse(string $source)
    {
        parse_str($source, $items);
        $this->items = $items;
    }

    /**
     * @return string
     */
    public function getSeparator(): string
    {
        return '&';
    }

    /**
     * @inheritDoc
     */
    public function updateSource(): void
    {
        $this->source = $this->strategy->updateQuery($this->items);
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
     * ["query"]=> "name=param&name2=para2m"
     * @param string $source
     */
    public function setSource(string $source): void
    {
        $this->source = $source;
        $this->parse($this->source);
    }

    /**
     * @param string|int $name
     * @return mixed
     */
    public function getValueByName(string|int $name): mixed
    {
        if (array_key_exists($name, $this->items)) {
            return $this->items[$name];
        }
        return null;
    }

    /**
     * @param string|int $name
     * @param mixed $value
     * @return mixed
     */
    public function setParam(string|int $name, mixed $value): static
    {
        $this->items[$name] = $value;
        return $this;
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
}