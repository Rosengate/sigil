<?php

namespace Ormi;

use Exedra\Routing\Call;
use Exedra\Support\Wireman\Wireman;

class CallHandler implements \Exedra\Contracts\Runtime\CallHandler
{
    protected $guarded = false;

    /**
     * @var Wireman
     */
    private Wireman $wireman;

    public function __construct(Wireman $wireman)
    {
        $this->wireman = $wireman;
    }

    public function handle(Call $call, array $args)
    {
        if (!$this->guarded || ($this->guarded))
            $args = $this->wireman->resolveCallable($call->getCallable());

        return call_user_func_array($call, $args);
    }
}
