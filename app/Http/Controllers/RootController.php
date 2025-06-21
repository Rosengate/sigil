<?php

namespace App\Http\Controllers;

class RootController extends \Sigil\Controller
{
    public function groupWeb()
    {
        return WebController::class;
    }

    public function groupApis()
    {
        return ApisController::class;
    }
}
