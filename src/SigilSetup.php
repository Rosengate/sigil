<?php

namespace Sigil;

use Exedra\Routeller\Cache\CacheInterface;

class SigilSetup
{
    /**
     * @var string
     */
    private string $rootController;

    /**
     * @var array
     */
    private array $middlewares;

    /**
     * @var CacheInterface|null
     */
    private ?CacheInterface $cache;

    /**
     * @var bool
     */
    private bool $autoReload;

    /**
     * @var array
     */
    private array $decorators;

    /**
     * SigilSetup constructor.
     * @param string $rootController initial controller class name
     * @param array $middlewares list of global middlewares
     * @param CacheInterface|null $cache
     * @param bool $autoReload reload on route chances
     * @param array $decorators array of decorator middleware
     */
    public function __construct(string $rootController,
                                array $middlewares = [],
                                CacheInterface $cache = null,
                                bool $autoReload = false,
                                array $decorators = [])
    {
        $this->rootController = $rootController;
        $this->middlewares = $middlewares;
        $this->cache = $cache;
        $this->autoReload = $autoReload;
        $this->decorators = $decorators;
    }

    public function getCacheInterface() : ?CacheInterface
    {
        return $this->cache;
    }

    public function getRootController() : string
    {
        return $this->rootController;
    }

    public function getMiddlewares() : array
    {
        return $this->middlewares;
    }

    /**
     * @return bool
     */
    public function isAutoReload(): bool
    {
        return $this->autoReload;
    }

    /**
     * @return array
     */
    public function getDecorators(): array
    {
        return $this->decorators;
    }
}
