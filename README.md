# ormi
Laravel-Exedra PHP 8 attributes based routing controller package

# Table of Contents
- Features
- Requirement
- Setup
- Basic Usages
- Routing Attributes
- Sub-routing / Group
- Middlewares
- State Property
- DI Method Injection
- Utilities
- Examples

## Features
- Couple the your routing with the controller class
- PHP 8 attributes based routing component
- Nested routing
- Provide a flexible ways to control/design your application through means like :
  - sub-routing based middleware
  - design a flag so it returns response differently
  - create your own attributes, and control it through your own middleware

## Requirements
- laravel
- rosengate/exedra
- php > 8
- this packages override your laravel Http Kernel and wholly use routing/controller/middleware component from rosengate/exedra 

## Setup
#### 1. Install package through composer
```
composer require offworks/ormi
```
#### 2. extends your `App\Http\Kernel` with `\Ormi\HttpKernel` (as this package overrides almost everything about routing, request dispatch)
```php
<?php
namespace App\Http;

class Kernel extends \Ormi\HttpKernel {
//...
```

Then implement the required method(s)

#### 3. Create the root controller
This controller serves as the root of your routing

```php
<?php
namespace App\Http\Controllers;

class RootController extends \Ormi\Controller
{
}
```

## Basic Usages
#### 1. Create a simple routing for front facing web
Define the group inside the root controller
```php
namespace App\Http\Controllers;

class RootController extends \Ormi\Controller
{
    public function groupWeb()
    {
        return WebController::class;
    }
}
```
Then create the controller

```php
namespace App\Http\Controllers;

use Exedra\Routeller\Attributes\Path;

#[Path('/')]
class WebController extends \Ormi\Controller
{
    #[Path('/')]
    public function get()
    {
        return view('welcome');
    }
}
```

## Routing Attributes
- `Path(path)` define the path for the current route / routing group (relatively)
- `Method(method|methods)` set method accessibility
- `Name(name)` set name
- `Tag(tag)` tag the current route
- `Middleware(middlewareClass)` add middleware
- `State(key, value)` set some arbitrary value
- `Config(key, value)` modify a config
- `Requestable(bool)` set whether this route is requestable or not
- `AsFailRoute` mark the selected route as fallback route
