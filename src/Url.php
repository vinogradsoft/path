<?php

namespace Vinograd\Path;

use Vinograd\Path\Exception\InvalidUrlException;

/**
 *
 *          |--------------------------------------absolute url-----------------------------------|
 *          |                                                                                     |
 *          |----------------base url----------------|------------------relative url--------------|
 *          |                                        |                                            |
 *          |                   authority            |          path            query    fragment |
 *          |      /‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾\|/‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾‾\ /‾‾‾‾‾‾‾‾‾\ /‾‾‾‾‾‾\ |
 *          http://grigor:password@vinograd.soft:8080/path/to/resource.json?query=value#fragment
 *          \__/   \___/  \_____/  \___________/ \__/                 \___/
 *         scheme  user   password     host      port                 suffix
 */
class Url extends AbstractPath
{
    /**
     * reserved array indices
     */
    const SCHEME = ':scheme';
    const USER = ':user';
    const PASSWORD = ':password';
    const HOST = ':host';
    const PORT = ':port';
    const FRAGMENT = '#';
    const PATH = ':path';
    const QUERY = '?';
    const SUFFIX = ':suffix';

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

    protected UrlStrategy $updateStrategy;

    protected UrlPath $path;
    protected UrlQuery $urlQuery;
    protected bool $isIdnToAscii = false;

    /**
     * @param string $source
     * @param bool $isIdnToAscii
     * @param UrlStrategy|null $updateStrategy
     */
    public function __construct(
        string       $source,
        bool         $isIdnToAscii = false,
        ?UrlStrategy $updateStrategy = null
    )
    {
        $this->initUrl($isIdnToAscii, $updateStrategy);
        parent::__construct($source);
    }

    /**
     * @param bool $isIdnToAscii
     * @param UrlStrategy|null $updateStrategy
     * @return $this
     */
    protected function initUrl(
        bool         $isIdnToAscii = false,
        ?UrlStrategy $updateStrategy = null
    ): static
    {
        $this->isIdnToAscii = $isIdnToAscii;
        $this->updateStrategy = $updateStrategy ?? new DefaultUrlStrategy();
        $this->path = UrlPath::createBlank($this->updateStrategy);
        $this->urlQuery = UrlQuery::createBlank($this->updateStrategy);
        return $this;
    }

    /**
     * @param bool $isIdnToAscii
     * @param UrlStrategy|null $updateStrategy
     * @return static
     */
    public static function createBlank(bool $isIdnToAscii = false, ?UrlStrategy $updateStrategy = null): static
    {
        static $prototype;
        if ($prototype === null) {
            $class = Url::class;
            /** @var Url $prototype */
            $prototype = unserialize(sprintf('O:%d:"%s":0:{}', \strlen($class), $class));
            $prototype->source = '';
            $prototype->baseUrl = '';
            $prototype->relativeUrl = '';
            $prototype->authorityUrl = '';
            $prototype->items = [];
        }
        return (clone $prototype)->initUrl($isIdnToAscii, $updateStrategy);
    }

    /**
     * @return bool
     */
    public function isConversionIdnToAscii(): bool
    {
        return $this->isIdnToAscii;
    }

    /**
     * @param bool $conversion
     * @return Url
     */
    public function setConversionIdnToAscii(bool $conversion): static
    {
        if ($this->isIdnToAscii === $conversion) {
            return $this;
        }
        $this->isIdnToAscii = $conversion;
        $this->authoritySate &= ~self::HOST_KEY;
        return $this;
    }

    /**
     * @param UrlStrategy $updateStrategy
     */
    public function setUpdateStrategy(UrlStrategy $updateStrategy): void
    {
        if ($this->updateStrategy === $updateStrategy) {
            return;
        }

        $this->updateStrategy = $updateStrategy;
        $this->path->setStrategy($this->updateStrategy);
        $this->urlQuery->setStrategy($this->updateStrategy);
        $this->authoritySate &= ~self::HOST_KEY;
        $this->authoritySate &= ~self::USER_KEY;
        $this->authoritySate &= ~self::PASSWORD_KEY;
        $this->relativeUrlState &= ~self::QUERY_KEY;
        $this->relativeUrlState &= ~self::PATH_KEY;
        $this->relativeUrlState &= ~self::FRAGMENT_KEY;
    }

    /**
     * @return UrlStrategy
     */
    public function getUpdateStrategy(): UrlStrategy
    {
        return $this->updateStrategy;
    }

    /**
     *
     */
    protected function resetState(): void
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
        $this->resetState();
        $this->path->reset();
        $this->urlQuery->reset();
        $this->items = [];
        $this->source = '';
        $this->baseUrl = null;
        $this->relativeUrl = null;
        $this->authorityUrl = null;
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function parse(string $source)
    {

        $this->reset();

        if (!$data = parse_url($source)) {
            throw new InvalidUrlException(sprintf('Valid url or part of it is expected. $source - %s', $source));
        }

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

        if (!isset($data['host']) && !isset($data['scheme'])) {
            throw new InvalidUrlException(sprintf('Valid url or part of it is expected. $source - %s', $source));
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
    public function getPath(): ?string
    {
        $path = $this->path->getSource();
        return !empty($path) ? $path : null;
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
     * @param array $items
     * @return void
     */
    public function setAll(array $items): void
    {
        $this->authoritySate &= ~self::HOST_KEY;
        $this->authoritySate &= ~self::USER_KEY;
        $this->authoritySate &= ~self::PASSWORD_KEY;
        $this->relativeUrlState &= ~self::QUERY_KEY;
        $this->relativeUrlState &= ~self::PATH_KEY;
        $this->relativeUrlState &= ~self::FRAGMENT_KEY;

        if (empty($items)) {
            $this->reset();
            return;
        }

        $this->items[self::SCHEME] = $items[self::SCHEME] ?? '';
        $this->items[self::USER] = $items[self::USER] ?? '';
        $this->items[self::PASSWORD] = $items[self::PASSWORD] ?? '';
        $this->items[self::HOST] = $items[self::HOST] ?? '';
        $this->items[self::PORT] = $items[self::PORT] ?? '';

        if (isset($items[self::PATH])) {
            $this->path->setAll($items[self::PATH]);
        } else {
            $this->path->reset();
        }

        if (isset($items[self::SUFFIX])) {
            $this->path->setSuffix($items[self::SUFFIX]);
        }

        if (isset($items[self::QUERY])) {
            $this->urlQuery->setAll($items[self::QUERY]);
        } else {
            $this->urlQuery->reset();
        }

        $this->items[self::FRAGMENT] = $items[self::FRAGMENT] ?? '';
    }

    /**
     * @return string|null
     */
    public function getQuery(): ?string
    {
        $query = $this->urlQuery->getSource();
        return !empty($query) ? $query : null;
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
    public function setParameter(string $name, mixed $value): static
    {
        $this->urlQuery->setParam($name, $value);
        $this->relativeUrlState &= ~self::QUERY_KEY;
        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getParameter(string $name): mixed
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
     * @return string|null
     */
    public function getSuffix(): ?string
    {
        return $this->path->getSuffix();
    }

    /**
     * @param string|null $suffix
     * @return Url
     */
    public function setSuffix(?string $suffix): static
    {
        if ($this->path->equalsSuffix($suffix)) {
            return $this;
        }
        $this->path->setSuffix($suffix);
        $this->relativeUrlState &= ~self::PATH_KEY;
        return $this;
    }

    /**
     * @return void
     */
    private function validateBaseUrlData(): void
    {
        if (empty($this->items[self::HOST])
            && (!empty($this->items[self::PORT]) || !empty($this->items[self::USER]) || !empty($this->items[self::PASSWORD]))) {
            throw new InvalidUrlException('Invalid parameters for the formation of the authority.');
        }
        if (empty($this->items[self::HOST]) && !empty($this->items[self::SCHEME])
            || !empty($this->items[self::HOST]) && empty($this->items[self::SCHEME])) {
            throw new InvalidUrlException('Incorrect parameters for forming url');
        }
    }

    /**
     * @inheritDoc
     */
    public function updateSource(bool $updateAbsoluteUrl = true, ?string $suffix = null): void
    {
        if ($suffix) {
            $this->setSuffix($suffix);
        }
        if ($this->authoritySate !== self::AUTHORITY_WHOLE) {
            $this->authorityUrl = $this->updateStrategy->updateAuthority($this->items, $this, $this->isIdnToAscii);
        }

        if ($this->authoritySate !== self::AUTHORITY_WHOLE || $this->schemeState === false) {
            $this->validateBaseUrlData();
            $this->baseUrl = $this->updateStrategy->updateBaseUrl($this->items, $this, $this->authorityUrl, $this->isIdnToAscii);
        }

        if (!($this->relativeUrlState & self::QUERY_KEY)) {
            $this->urlQuery->updateSource();
        }

        if (!($this->relativeUrlState & self::PATH_KEY)) {
            $this->path->updateSource();
        }

        if ($this->relativeUrlState !== self::RELATIVE_URL_WHOLE) {
            $this->relativeUrl = $this->updateStrategy->updateRelativeUrl(
                $this->items,
                $this,
                (string)$this->path,
                (string)$this->urlQuery,
                $this->path,
                $this->urlQuery,
                $suffix
            );
        }
        if (!$updateAbsoluteUrl) {
            $this->resetState();
            return;
        }
        if ($this->authoritySate !== self::AUTHORITY_WHOLE
            || $this->schemeState === false
            || $this->relativeUrlState !== self::RELATIVE_URL_WHOLE) {

            $this->source = $this->updateStrategy->updateAbsoluteUrl(
                $this->items,
                $this,
                (string)$this->relativeUrl,
                $this->baseUrl,
                !empty($this->path->getSource()),
                $this->isIdnToAscii,
                $suffix
            );

        }

        $this->resetState();
    }
}