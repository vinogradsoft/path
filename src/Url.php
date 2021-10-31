<?php

namespace Vinograd\Path;

/**
 *
 *    |----------------base url-----------------|--------------relative url------------|
 *    |                                         |                                      |
 *    |scheme              authority            |      path          query    fragment |
 *    |/‾‾\    /‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾\|/‾‾‾‾‾‾‾‾‾‾‾‾‾‾\ /‾‾‾‾‾‾‾‾‾\ /‾‾‾‾‾‾\ |
 *     http://grigor:password@vinograd.soft:8080/path/to/resource?query=value#fragment
 *            \___/  \_____/  \___________/ \__/
 *            user  password      host      port
 */
class Url extends AbstractPath
{
    /**
     * reserved array indices
     */
    const SCHEME = 0;
    const USER = 1;
    const PASSWORD = 2;
    const HOST = 3;
    const PORT = 4;
    const FRAGMENT = 5;

    /**
     * key state
     */
    protected const USER_KEY = 1 << 0;
    protected const PASSWORD_KEY = 1 << 1;
    protected const HOST_KEY = 1 << 2;
    protected const PORT_KEY = 1 << 3;

    protected const PATH_KEY = 1 << 0;
    protected const QUERY_KEY = 1 << 1;
    protected const FRAGMENT_KEY = 1 << 2;

    protected const AUTHORITY_WHOLE = self::USER_KEY | self::PASSWORD_KEY | self::HOST_KEY | self::PORT_KEY;
    protected const RELATIVE_URL_WHOLE = self::PATH_KEY | self::QUERY_KEY | self::FRAGMENT_KEY;

    /**
     * current state
     */
    protected int $authoritySate = self::AUTHORITY_WHOLE;
    protected int $relativeUrlState = self::RELATIVE_URL_WHOLE;
    protected bool $schemeState = true;

    protected ?string $baseUrl = null;
    protected ?string $relativeUrl = null;
    protected ?string $authorityUrl = null;

    protected UpdateStrategy $currentUpdateStrategy;
    protected UpdateStrategy $encodeUpdateStrategy;
    protected UpdateStrategy $rawUpdateStrategy;
    protected UrlPath $path;
    protected UrlQuery $urlQuery;
    protected bool $encodeState = true;

    /**
     * @param string $source
     * @param bool $encodeState
     * @param UpdateStrategy|null $encodeUpdateStrategy
     * @param UpdateStrategy|null $rawUpdateStrategy
     */
    public function __construct(
        string          $source,
        bool            $encodeState = true,
        ?UpdateStrategy $encodeUpdateStrategy = null,
        ?UpdateStrategy $rawUpdateStrategy = null
    )
    {
        $this->encodeUpdateStrategy = $encodeUpdateStrategy ?? new EncodeUpdateStrategy();
        $this->rawUpdateStrategy = $rawUpdateStrategy ?? new RawUpdateStrategy();
        $this->encodeState = $encodeState;

        $this->currentUpdateStrategy = $encodeState ? $this->encodeUpdateStrategy : $this->rawUpdateStrategy;
        $this->path = $this->createPath($this->currentUpdateStrategy);
        $this->urlQuery = $this->createQuery($this->currentUpdateStrategy);
        parent::__construct($source);
    }

    /**
     * @return bool
     */
    public function isEncode(): bool
    {
        return $this->encodeState;
    }

    /**
     * @param bool $encode
     */
    public function setEncode(bool $encode): void
    {
        if ($this->encodeState === $encode) {
            return;
        }

        $this->currentUpdateStrategy = $encode ? $this->encodeUpdateStrategy : $this->rawUpdateStrategy;
        $this->path->setStrategy($this->currentUpdateStrategy);
        $this->urlQuery->setStrategy($this->currentUpdateStrategy);
        $this->encodeState = $encode;
        $this->discardOnNonWhole();
    }

    /**
     * @param UpdateStrategy $encodeUpdateStrategy
     */
    public function setEncodeUpdateStrategy(UpdateStrategy $encodeUpdateStrategy): void
    {
        $this->encodeUpdateStrategy = $encodeUpdateStrategy;
        if (!$this->encodeState) {
            return;
        }

        $this->currentUpdateStrategy = $encodeUpdateStrategy;
        $this->path->setStrategy($this->currentUpdateStrategy);
        $this->urlQuery->setStrategy($this->currentUpdateStrategy);
        $this->discardOnNonWhole();
    }

    /**
     * @param UpdateStrategy $rawUpdateStrategy
     */
    public function setRawUpdateStrategy(UpdateStrategy $rawUpdateStrategy): void
    {
        $this->rawUpdateStrategy = $rawUpdateStrategy;
        if ($this->encodeState) {
            return;
        }

        $this->currentUpdateStrategy = $rawUpdateStrategy;
        $this->path->setStrategy($this->currentUpdateStrategy);
        $this->urlQuery->setStrategy($this->currentUpdateStrategy);
        $this->discardOnNonWhole();
    }

    /**
     * @return void
     */
    protected function discardOnNonWhole(): void
    {
        $this->authoritySate &= ~self::HOST_KEY;
        $this->authoritySate &= ~self::USER_KEY;
        $this->authoritySate &= ~self::PASSWORD_KEY;
        $this->relativeUrlState &= ~self::QUERY_KEY;
        $this->relativeUrlState &= ~self::PATH_KEY;
        $this->relativeUrlState &= ~self::FRAGMENT_KEY;
    }

    /**
     *
     */
    public function resetState(): void
    {
        $this->authoritySate = self::AUTHORITY_WHOLE;
        $this->schemeState = true;
        $this->relativeUrlState = self::RELATIVE_URL_WHOLE;
    }

    /**
     * @param string $source
     */
    public function setSource(string $source): void
    {
        $this->resetState();
        $this->parse(rawurldecode(rtrim($source, $this->getSeparator())));
        $this->updateSource();
    }

    /**
     * @return $this
     */
    public function reset(): static
    {
        $this->items[self::SCHEME] = '';
        $this->items[self::USER] = '';
        $this->items[self::PASSWORD] = '';
        $this->items[self::HOST] = '';
        $this->items[self::PORT] = '';
        $this->path->reset();
        $this->urlQuery->reset();
        $this->items[self::FRAGMENT] = '';
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function parse(string $source)
    {

        $this->reset();
        $data = parse_url($source);

        if (isset($data['scheme'])) {
            $this->items[self::SCHEME] = $data['scheme'];
            $this->schemeState = false;
        }
        if (isset($data['user'])) {
            $this->items[self::USER] = rawurldecode($data['user']);
            $this->authoritySate &= ~self::USER_KEY;
        }
        if (isset($data['pass'])) {
            $this->items[self::PASSWORD] = rawurldecode($data['pass']);
            $this->authoritySate &= ~self::PASSWORD_KEY;
        }

        if (isset($data['host'])) {
            $this->items[self::HOST] = rawurldecode($data['host']);
            $this->authoritySate &= ~self::HOST_KEY;
        }

        if (isset($data['port'])) {
            $this->items[self::PORT] = $data['port'];
            $this->authoritySate &= ~self::PORT_KEY;
        }

        if (isset($data['path'])) {
            $this->path->setSource($data['path']);
            $this->relativeUrlState &= ~self::PATH_KEY;
        }

        if (isset($data['query'])) {
            $this->urlQuery->setSource($data['query']);
            $this->relativeUrlState &= ~self::QUERY_KEY;
        }

        if (isset($data['fragment'])) {
            $this->items[self::FRAGMENT] = rawurldecode($data['fragment']);
            $this->relativeUrlState &= ~self::FRAGMENT_KEY;
        }
    }

    /**
     * @inheritDoc
     */
    public function getSeparator(): string
    {
        return '/';
    }

    /**
     * @return string|null
     */
    public function getAuthority(): ?string
    {
        return $this->authorityUrl;
    }

    /**
     * @return string|null
     */
    public function getHost(): ?string
    {
        return !empty($this->items[self::HOST]) ? $this->items[self::HOST] : null;
    }

    /**
     * @param string $host
     * @return $this
     */
    public function setHost(string $host): static
    {
        $this->items[self::HOST] = $host;
        $this->authoritySate &= ~self::HOST_KEY;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPort(): ?string
    {
        return !empty($this->items[self::PORT]) ? $this->items[self::PORT] : null;
    }

    /**
     * @param string $port
     * @return $this
     */
    public function setPort(string $port): static
    {
        $this->items[self::PORT] = $port;
        $this->authoritySate &= ~self::PORT_KEY;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPathString(): ?string
    {
        $path = $this->path->getSource();
        return !empty($path) ? $path : null;
    }

    /**
     * @return UrlPath|null
     */
    public function getPath(): ?UrlPath
    {
        $path = $this->path->getSource();
        if (empty($path)) {
            return null;
        }
        return clone $this->path;
    }

    /**
     * @param string $pathString
     * @return $this
     */
    public function setPath(string $pathString): static
    {
        $this->path->setSource($pathString);
        $this->relativeUrlState &= ~self::PATH_KEY;
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setArrayPath(array $data): static
    {
        $this->path->setAll($data);
        $this->relativeUrlState &= ~self::PATH_KEY;
        return $this;
    }

    /**
     * @param UpdateStrategy $strategy
     * @return UrlPath
     */
    protected function createPath(UpdateStrategy $strategy): UrlPath
    {
        static $prototypePath;
        if (!$prototypePath instanceof UrlPath) {
            $class = UrlPath::class;
            /** @var UrlPath $prototypePath */
            $prototypePath = unserialize(sprintf('O:%d:"%s":0:{}', \strlen($class), $class));
            $prototypePath->setStrategy($strategy);
        } elseif (!$prototypePath->equalsStrategy($strategy)) {
            $prototypePath->setStrategy($strategy);
        }
        return clone $prototypePath;
    }

    /**
     * @param UpdateStrategy $strategy
     * @return UrlQuery
     */
    protected function createQuery(UpdateStrategy $strategy): UrlQuery
    {
        static $prototypeQuery;
        if (!$prototypeQuery instanceof UrlQuery) {
            $class = UrlQuery::class;
            /** @var UrlQuery $prototypeQuery */
            $prototypeQuery = unserialize(sprintf('O:%d:"%s":0:{}', \strlen($class), $class));
            $prototypeQuery->setStrategy($strategy);
        } elseif (!$prototypeQuery->equalsStrategy($strategy)) {
            $prototypeQuery->setStrategy($strategy);
        }
        return clone $prototypeQuery;
    }

    /**
     * @return string|null
     */
    public function getQueryString(): ?string
    {
        $query = $this->urlQuery->getSource();
        return !empty($query) ? $query : null;
    }

    /**
     * @return UrlQuery|null
     */
    public function getQuery(): ?UrlQuery
    {
        if (empty($this->urlQuery->getSource())) {
            return null;
        }
        return clone $this->urlQuery;
    }

    /**
     * @param string $queryString
     * @return $this
     */
    public function setQuery(string $queryString): static
    {
        $this->urlQuery->setSource($queryString);
        $this->relativeUrlState &= ~self::QUERY_KEY;
        return $this;
    }

    /**
     * @param array $query
     * @return $this
     */
    public function setArrayQuery(array $query): static
    {
        $this->urlQuery->setAll($query);
        $this->relativeUrlState &= ~self::QUERY_KEY;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFragment(): ?string
    {
        return !empty($this->items[self::FRAGMENT]) ? $this->items[self::FRAGMENT] : null;
    }

    /**
     * @param string $fragment
     * @return $this
     */
    public function setFragment(string $fragment): static
    {
        $this->items[self::FRAGMENT] = $fragment;
        $this->relativeUrlState &= ~self::FRAGMENT_KEY;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return !empty($this->items[self::PASSWORD]) ? $this->items[self::PASSWORD] : null;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword(string $password): static
    {
        $this->items[self::PASSWORD] = $password;
        $this->authoritySate &= ~self::PASSWORD_KEY;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUser(): ?string
    {
        return !empty($this->items[self::USER]) ? $this->items[self::USER] : null;
    }

    /**
     * @param string $user
     * @return $this
     */
    public function setUser(string $user): static
    {
        $this->items[self::USER] = $user;
        $this->authoritySate &= ~self::USER_KEY;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getScheme(): ?string
    {
        return !empty($this->items[self::SCHEME]) ? $this->items[self::SCHEME] : null;
    }

    /**
     * @param string $scheme
     * @return $this
     */
    public function setScheme(string $scheme): static
    {
        $this->items[self::SCHEME] = $scheme;
        $this->schemeState = false;
        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function addParameter(string $name, mixed $value): static
    {
        $this->urlQuery->setParam($name, $value);
        $this->relativeUrlState &= ~self::QUERY_KEY;
        return $this;
    }

    /**
     * @param string|int $name
     * @return mixed
     */
    public function getParameter(string|int $name): mixed
    {
        return $this->urlQuery->getValueByName($name);
    }

    /**
     * @return string|null
     */
    public function getRelativeUrl(): ?string
    {
        return $this->relativeUrl;
    }

    /**
     * @return string|null
     */
    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    /**
     * @inheritDoc
     */
    public function updateSource(bool $updateAbsoluteUrl = true): void
    {
        if ($this->authoritySate !== self::AUTHORITY_WHOLE) {
            $this->authorityUrl = $this->currentUpdateStrategy->updateAuthority($this->items, $this);
        }

        if ($this->authoritySate !== self::AUTHORITY_WHOLE || $this->schemeState === false) {
            $this->baseUrl = $this->currentUpdateStrategy->updateBaseUrl($this->items, $this, $this->authorityUrl);
        }

        if (!($this->relativeUrlState & self::QUERY_KEY)) {
            $this->urlQuery->updateSource();
        }

        if (!($this->relativeUrlState & self::PATH_KEY)) {
            $this->path->updateSource();
        }

        if ($this->relativeUrlState !== self::RELATIVE_URL_WHOLE) {
            $this->relativeUrl = $this->currentUpdateStrategy->updateRelativeUrl(
                $this->items,
                $this,
                (string)$this->path,
                (string)$this->urlQuery,
                $this->path,
                $this->urlQuery
            );
        }
        if (!$updateAbsoluteUrl) {
            $this->resetState();
            return;
        }
        if ($this->authoritySate !== self::AUTHORITY_WHOLE
            || $this->schemeState === false
            || $this->relativeUrlState !== self::RELATIVE_URL_WHOLE) {

            $this->source = $this->currentUpdateStrategy->updateAbsoluteUrl(
                $this->items,
                $this,
                (string)$this->relativeUrl,
                $this->baseUrl,
                !empty($this->path->getSource())
            );

        }

        $this->resetState();
    }
}