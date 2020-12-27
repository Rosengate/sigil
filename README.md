# Sigil
Laravel-Exedra PHP 8 attributes based routing controller package

# Table of Contents
- [Features](#features)
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
- Drawbacks
- Feedbacks
- Why
- License

## <a name='features'></a> Features
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
composer require rosengate/sigil
```
#### 2. extends your `App\Http\Kernel` with `Sigil\HttpKernel` (as this package uses it's own routing and request dispatch)

```php
<?php
namespace App\Http;

use App\Http\Controllers\RootController;
use Sigil\KernelSetup;

class Kernel extends \Sigil\HttpKernel {
    //.. .
    public function getSigilSetup() : KernelSetup
    {
        return new KernelSetup(
            RootController::class, // the initial root controller class,
            middlewares: $this->middleware // use the listed kernel
        );
    }
    //...
}
```

Then implement the required method(s)

#### 3. Create the root controller
This controller serves as the root of your routing

```php
<?php
namespace App\Http\Controllers;

class RootController extends \Sigil\Controller
{
}
```

## Basic Usages
#### 1. Create a simple routing for front facing web
Define the group inside the root controller

```php
namespace App\Http\Controllers;

class RootController extends \Sigil\Controller
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
class WebController extends \Sigil\Controller
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
use Exedra\Routeller\Attributes\Path;

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

### Global middlewares

### Group/route based middlewares

\App\Http\Middleware\EncryptCookies::class,
\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
\Illuminate\Session\Middleware\StartSession::class,
// \Illuminate\Session\Middleware\AuthenticateSession::class,
\Illuminate\View\Middleware\ShareErrorsFromSession::class,
\App\Http\Middleware\VerifyCsrfToken::class,

```php
<?php
use Exedra\Routeller\Attributes\Path;
use Exedra\Routeller\Attributes\Middleware;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use App\Http\Middleware\EncryptCookies;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

#[Middleware(EncryptCookies::class)]
#[Middleware(AddQueuedCookiesToResponse::class)]
#[Middleware(StartSession::class)]
class WebController
{
    #[Path('/contact-us')]
    #[Middleware(VerifyCsrfToken::class)]
    public function postContactUs()
    {
    }
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
use Sigil\Context;
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
use Sigil\Context;
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
use Exedra\Routeller\Attributes\Flag;
use Exedra\Routeller\Attributes\Path;
use Sigil\Context;

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
use Sigil\Context;
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
use Exedra\Routeller\Attributes\Path;

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

## Utilities
### Route-Model finder / registry

##### Installation
Add ```RouteModelMiddleware``` in your ```App\Http\Kernel```
```php
<?php
use Exedra\Routeller\Attributes\Path;
use Sigil\Utilities\Attributes\Model;

#[Path('/authors/:author-id')]
#[Model(Author::class, 'author-id')]
class AuthorApiController
{
    public function get(Author $author)
    {
        return $author;
    }
}
```

### Transformer
PHP League Fractal transformer. Transform your api response from your laravel model/collection. 
Make sure to have fractal required.

##### Installation
Add ```TransformerMiddleware``` in your ```App\Http\Kernel```
```php
<?php
use Exedra\Routeller\Attributes\Path;
use Sigil\Utilities\Attributes\Model;

#[Path('/orders/:order-id')]
#[Model(Order::class, 'order-id')]
class OrderApiController
{
    #[Transform(OrderTransformer::class)]
    public function get(Order $order)
    {
        return $order;
    }
}
```

### Renderer
Handle the content returns of your controller action by defining a renderer that extends `Sigil\Contracts\Renderer`.

##### Installation
Provide a `Sigil\Utilities\Middlewares\RendererDecorator` middleware through your `Sigil\KernelSetup` in your `App\Http\Kernel` extending method.

```php
    use Sigil\Utilities\Middlewares\RendererDecorator;

    //.. your App\Http\Kernel
    public function getSigilSetup() : KernelSetup
    {
        return new KernelSetup(
            RootController::class, // the initial root controller class,
            middlewares: $this->middleware, // use the listed kernel,
            decorators: [RendererDecorator::class]
        );
    }
```

## Drawbacks
As this package completely use a different component for routing, in general it will be incompatible with any other packages 
that make use of laravel routing or the `routes` folder. Also these components as of now :
- Url Generator
- Redirection with route name

## Why
I made `rosengate/exedra` back 4 years ago because i couldn't find a framework that can exactly do what I wanted, like hierarchically nest a routing beneath another routing. 
Also `exedra` never want to be another full-fledged framework. It's just a microframework and I always promote the use of tons of amazing php packages out there. 
Then I built a phpdoc based routing controller component and since then writing a code with `exedra` became a bliss than ever. 
But building things from microframework can be daunting as I always needed an ORM, validation, error handling and so on (I always find myself using Elqouent)

I use exedra with laravel on one of my project and I am starting to think that this is kinda possible. 
Then PHP8 came with a news so good i've been waiting for years. Attributes/Annotation. So I decided to just port it for laravel and see how it goes here. <3

## Feedbacks
- Feel free to throw in feedbacks through github issues.
- I am planning to find a way to integrate with `Illuminate\Contracts\Routing\UrlGenerator` soon.

## License
[MIT License](LICENSE)
