<?php

namespace Vinograd\Path;

class UrlQuery extends AbstractPath
{
    protected int $encodingType = PHP_QUERY_RFC1738;

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
     * @return string
     */
    public function getKeyValueSeparator(): string
    {
        return '=';
    }

    /**
     * @inheritDoc
     */
    public function updateSource(): void
    {
        $this->source = http_build_query($this->items, "", "&", $this->encodingType);
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
    public function getParamByName(string|int $name): mixed
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
     * @param int $encodingType PHP_QUERY_RFC1738 | PHP_QUERY_RFC3986
     */
    public function setEncodingType(int $encodingType)
    {
        $this->encodingType = $encodingType;
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