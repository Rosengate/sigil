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
- Meta Information
  - State
  - Series
  - Flag
  - Custom Meta Information
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
- Laravel
- php > 8
- this packages override your laravel Http Kernel and wholly use routing/controller/middleware component from `rosengate/exedra` 

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
- `Name(name)` set route name
- `Tag(tag)` tag the current route
- `Middleware(middlewareClass)` add middleware
- `State(key, value)` a mutable meta information
- `Series(key, value)` an additive meta information
- `Flag(flag)` an array of meta information
- `Config(key, value)` modify a config
- `Requestable(bool)` set whether this route is requestable or not
- `AsFailRoute` mark the selected route as fallback route

## Sub-routing / Group
This package allows you nest your routing beneath another routing indefinitely. Your routing uri/path is relative as it goes down the depth.

```php
<?php
class RootController
{
    public function groupWeb()
    {
        return WebController::class;
    }
    
    public function groupAdmin()
    {
        return AdminController::class;
    }
}

#[Path('/admin')]
class AdminController
{
    #[Path('/dashboard')]
    public function getDashboard()
    {
    }
}

#[Path('/')]
class WebController
{
    public function groupEnquiries()
    {
        return EnquiriesController::class;
    }
}

#[Path('/enquiries')]
class EnquiriesController
{
    #[Path('/form')]
    public function getForm()
    {
    }
    
    #[Path('/form')]
    public function postForm()
    {
    }
}

```

Above routing will give a result like :
```
GET  /admin/dashboard
GET  /enquries/form
POST /enquries/form
```

## Middlewares
Feel free to use your laravel middlewares at it still follows the same signature, and the constructor arguments are also injected with laravel di container.

```php
<?php

#[Middleware(ValidatePostSize::class)]
#[Middleware(EncryptCookies::class)]
#[Middleware(AddQueuedCookiesToResponse::class)]
class RootController
{
}
```

## Meta Information
The nested nature of this framework allows us to design our app as flexible as we wish. However, there are three types of 
information we can use for this purpose.

### State
A mutable key based information.

```php
<?php
//...
use Ormi\Context;
use Exedra\Routeller\Attributes\State;
use Exedra\Routeller\Attributes\Path;

#[Path('/')]
#[State('is_ajax', true)]
class WebController
{
    #[Path('/contact-us')]
    #[State('is_ajax', false)]
    public function getContactUs(Context $context)
    {
        var_dump($context->getState('is_ajax')); // false
    }
}
```

### Series
An additive / array based key specific information. New information is appended instead of mutated.
```php
<?php
use Ormi\Context;
use Exedra\Routeller\Attributes\Path;
use Exedra\Routeller\Attributes\Series;

#[Series('roles', 'admin')]
class AdminController
{
    #[Path('/dashboard')]
    #[Series('roles', 'librarian')]
    public function getLibrary(Context $context)
    {
        var_dump($context->getSeries('roles')); // prints ['admin', 'staff']
    }
    
    #[Series('roles', 'accountant')]
    #[Path('/billing')]
    public function getOrders(Context $context)
    {
        var_dump($context->getSeries('roles')); // prints ['admin', 'accountant']
    }
}
```

### Flag
An array of flags / information. Similiar to series, but more simpler.

```php
<?php

#[Flag('authenticated')]
class AdminController
{
    #[Path('/dashboard')]
    #[Flag('is_beta')]
    public function getDashboard(Context $context)
    {
        var_dump($context->getFlags()); // ['authenticated', 'is_beta']
    }
}
```


### Usage of Meta Information
Meta information are best used with a middleware where you could control the flow/behaviour/design of your application by your defined metas.

For eg, let's use some of the meta information we wrote above and write some pseudo codes.
```php
use Ormi\Context;
use App\Models\User;

class RootController
{
    public function middleware($request, $next, Context $context)
    {
        if ($context->hasFlag('authenticated')) {
            // do some authentication
            if (!session()->has('user_id'))
                throw new NotAuthenticatedException();
                
            $user = User::find(session()->get('user_id'));
            
            if ($context->hasFlag('is_beta')) {
                if (!$user->isBetaAllowed())
                    throw new NoAccessException();
            }
            
            if ($context->hasSeries('roles'))
                if (!in_array($user->role, $context->getSeries('roles')))
                    throw new NoAccessException();
        }
         
        return $next($request);
    }
}
```

### DI Method Injection
The DI wiring of this package make use of laravel container registry. So, anything that you registered on app() container can also be retrieved here.

For eg :

```php
<?php

#[Path('/books/:book-id')]
class BookApiController
{
    public function middleware($request, $next)
    {
        app()->instance(Book::class, Book::findOrFail($request->route('book-id')));
        
        return $next($request);
    }
    
    #[Path('/')]
    public function get(Book $book)
    {
        return $book;
    }
    
    #[Path('/')]
    public function post(Book $book, $request)
    {
        $book->author = $request->author;
        $book->isbn = $request->isbn;
        $book->save();
        
        return $book;
    }
}
```
