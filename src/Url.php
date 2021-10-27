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

    protected UpdateStrategy $strategy;
    protected Path $path;
    protected UrlQuery $urlQuery;

    /**
     * @var int PHP_QUERY_RFC1738 | PHP_QUERY_RFC3986
     */
    protected int $encodingType = PHP_QUERY_RFC1738;

    /**
     * @param string $source
     * @param UpdateStrategy|null $strategy
     */
    public function __construct(string $source, ?UpdateStrategy $strategy = null)
    {
        if ($strategy !== null) {
            $this->strategy = $strategy;
        } else {
            $this->strategy = new AllUpdateStrategy();
        }
        $this->path = $this->createPath();
        $this->urlQuery = $this->createQuery();
        parent::__construct($source);
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
        $this->source = rawurldecode(rtrim($source, $this->getSeparator()));
        $this->parse($this->source);
        $this->updateSource(false);
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
            $this->items[self::USER] = $data['user'];
            $this->authoritySate &= ~self::USER_KEY;
        }
        if (isset($data['pass'])) {
            $this->items[self::PASSWORD] = $data['pass'];
            $this->authoritySate &= ~self::PASSWORD_KEY;
        }

        if (isset($data['host'])) {
            $this->items[self::HOST] = $data['host'];
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
            $this->items[self::FRAGMENT] = $data['fragment'];
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
     * @return Path|null
     */
    public function getPath(): ?Path
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
     * @return Path
     */
    protected function createPath(): Path
    {
        static $prototypePath;
        if ($prototypePath === null) {
            $class = Path::class;
            $prototypePath = unserialize(sprintf('O:%d:"%s":0:{}', \strlen($class), $class));
        }
        return clone $prototypePath;
    }

    /**
     * @return UrlQuery
     */
    protected function createQuery(): UrlQuery
    {
        static $prototypeQuery;
        if ($prototypeQuery === null) {
            $class = UrlQuery::class;
            $prototypeQuery = unserialize(sprintf('O:%d:"%s":0:{}', \strlen($class), $class));
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
        return $this->urlQuery->getParamByName($name);
    }

    /**
     * @param int $encodingType PHP_QUERY_RFC1738 | PHP_QUERY_RFC3986
     */
    public function setEncodingType(int $encodingType)
    {
        $this->encodingType = $encodingType;
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
            $this->authorityUrl = $this->strategy->updateAuthority($this->items, $this);
        }

        if ($this->authoritySate !== self::AUTHORITY_WHOLE || $this->schemeState === false) {
            $this->baseUrl = $this->strategy->updateBaseUrl($this->items, $this, $this->authorityUrl);
        }

        if (!($this->relativeUrlState & self::QUERY_KEY)) {
            $this->strategy->updateQuery($this->urlQuery, $this->encodingType);
        }

        if (!($this->relativeUrlState & self::PATH_KEY)) {
            $this->strategy->updatePath($this->path);
        }

        if ($this->relativeUrlState !== self::RELATIVE_URL_WHOLE) {
            $this->relativeUrl = $this->strategy->updateRelativeUrl(
                $this->items,
                $this,
                (string)$this->path,
                $this->path,
                (string)$this->urlQuery,
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

            $this->source = $this->strategy->updateAbsoluteUrl(
                $this->items,
                $this,
                $this->relativeUrl,
                $this->baseUrl,
                !empty($this->path->getSource())
            );

        }

        $this->resetState();
    }
}