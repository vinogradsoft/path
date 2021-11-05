<?php

namespace Vinograd\Path;

class UrlQuery extends AbstractPath
{
    protected UrlQueryStrategy $strategy;

    /**
     * @param string $source
     * @param UrlQueryStrategy|null $strategy
     */
    public function __construct(string $source, ?UrlQueryStrategy $strategy = null)
    {
        $this->initUrlQuery($strategy);
        parent::__construct($source);
    }

    /**
     * @param UrlQueryStrategy|null $strategy
     * @return $this
     */
    protected function initUrlQuery(?UrlQueryStrategy $strategy = null): static
    {
        $this->strategy = $strategy ?? new DefaultUrlQueryStrategy();
        return $this;
    }

    /**
     * @param UrlQueryStrategy|null $strategy
     * @return static
     */
    public static function createBlank(?UrlQueryStrategy $strategy = null): static
    {
        static $prototypeQuery;
        if (!$prototypeQuery instanceof UrlQuery) {
            $class = UrlQuery::class;
            /** @var UrlQuery $prototypeQuery */
            $prototypeQuery = unserialize(sprintf('O:%d:"%s":0:{}', \strlen($class), $class));
            $prototypeQuery->source = '';
            $prototypeQuery->items = [];
        }
        return (clone $prototypeQuery)->initUrlQuery($strategy);
    }

    /**
     * @param UrlQueryStrategy $strategy
     */
    public function setStrategy(UrlQueryStrategy $strategy): void
    {
        $this->strategy = $strategy;
    }

    /**
     * @return UrlQueryStrategy
     */
    public function getStrategy(): UrlQueryStrategy
    {
        return $this->strategy;
    }

    /**
     * @param string $source
     */
    protected function parse(string $source)
    {
        parse_str($source, $items);
        $this->items = $items;
        $this->updateSource();
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
     * @param UrlQueryStrategy $strategy
     * @return bool
     */
    public function equalsStrategy(UrlQueryStrategy $strategy): bool
    {
        return $this->strategy === $strategy;
    }

    /**
     * ["query"]=> "name=param&name2=para2m"
     * @param string $source
     */
    public function setSource(string $source): void
    {
        $this->parse($source);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getValueByName(string $name): mixed
    {
        if (array_key_exists($name, $this->items)) {
            return $this->items[$name];
        }
        return null;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function setParam(string $name, mixed $value): static
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

    /**
     * @inheritDoc
     */
    public function setAll(array $items): void
    {
        $this->items = $items;
    }
}