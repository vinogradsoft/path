<?php

namespace Vinograd\Path;

use Vinograd\Path\Exception\InvalidPathException;

class UrlPath extends AbstractPath
{
    protected UrlPathStrategy $strategy;
    protected ?string $suffix = null;

    /**
     * @param string $source
     * @param UrlPathStrategy|null $strategy
     */
    public function __construct(string $source, ?UrlPathStrategy $strategy = null)
    {
        $this->assertNotEmpty($source);
        $this->initUrlPath($strategy);
        parent::__construct($source);
    }

    /**
     * @param $source
     * @return void
     */
    private function assertNotEmpty($source)
    {
        if (empty($source)) {
            throw new InvalidPathException('Source UrlPath cannot be empty.');
        }
    }

    /**
     * @param UrlPathStrategy|null $strategy
     * @return $this
     */
    protected function initUrlPath(?UrlPathStrategy $strategy = null): static
    {
        $this->strategy = $strategy ?? new DefaultUrlPathStrategy();
        return $this;
    }

    /**
     * @param UrlPathStrategy|null $strategy
     * @return static
     */
    public static function createBlank(?UrlPathStrategy $strategy = null): static
    {
        static $prototypePath;
        if (!$prototypePath instanceof UrlPath) {
            $class = UrlPath::class;
            /** @var UrlPath $prototypePath */
            $prototypePath = unserialize(sprintf('O:%d:"%s":0:{}', \strlen($class), $class));
            $prototypePath->items = [];
            $prototypePath->source = '';
        }
        return (clone $prototypePath)->initUrlPath($strategy);
    }

    /**
     * @param UrlPathStrategy $strategy
     */
    public function setStrategy(UrlPathStrategy $strategy): void
    {
        $this->strategy = $strategy;
    }

    /**
     * @return UrlPathStrategy
     */
    public function getStrategy(): UrlPathStrategy
    {
        return $this->strategy;
    }

    /**
     * @inheritDoc
     */
    protected function parse(string $source)
    {
        if (empty($source)) {
            $this->reset();
            return;
        }
        $this->items = explode('/', $source);
        $this->updateSource();
    }

    /**
     * @inheritDoc
     */
    public function getSeparator(): string
    {
        return '/';
    }

    /**
     * @param UrlStrategy $strategy
     * @return bool
     */
    public function equalsStrategy(UrlStrategy $strategy): bool
    {
        return $this->strategy === $strategy;
    }

    /**
     * @param string|null $suffix
     * @return bool
     */
    public function equalsSuffix(?string $suffix): bool
    {
        return $this->suffix === $suffix;
    }

    /**
     * @return string|null
     */
    public function getSuffix(): ?string
    {
        return $this->suffix;
    }

    /**
     * @param string|null $suffix
     */
    public function setSuffix(?string $suffix): void
    {
        $this->suffix = $suffix;
    }

    /**
     * @return void
     */
    public function updateSource(): void
    {
        $this->source = $this->strategy->updatePath($this->items, $this->suffix);
    }

    /**
     * @inheritDoc
     */
    public function reset(): static
    {
        $this->source = '';
        $this->items = [];
        $this->suffix = null;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setSource(string $source): void
    {
        $this->parse(rtrim($source, $this->getSeparator()));
    }

    /**
     * @inheritDoc
     */
    public function setAll(array $items): void
    {
        $this->items = $items;
    }
}