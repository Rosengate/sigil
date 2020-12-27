<?php

namespace Sigil\Contracts;

use Illuminate\Http\Response;

/**
 * Contents renderer
 * Render the contents as http Response
 * @package Sigil\Contracts
 */
interface Renderer
{
    public function isRenderable($contents) : bool;

    public function render($contents) : Response;
}
