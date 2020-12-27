<?php

namespace Sigil;

use Exedra\Routeller\Cache\CacheInterface;

class KernelSetup
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
     * KernelSetup constructor.
     * @param string $rootController initial controller class name
     * @param array $middlewares list of global middlewares
     * @param CacheInterface|null $cache
     * @param bool $autoReload reload on route chances
     */
    public function __construct(string $rootController, array $middlewares = [], CacheInterface $cache = null, bool $autoReload = false)
    {
        $this->rootController = $rootController;
        $this->middlewares = $middlewares;
        $this->cache = $cache;
        $this->autoReload = $autoReload;
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
}
