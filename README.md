# Sigil
Laravel-Exedra PHP 8 attributes based routing controller package

# Table of Contents
- [Features](#features)
- [Requirements](#requirements)
- [Setup](#setup)
- [Basic Usages](#usages)
- [Routing Conventions](#conventions)
- [Routing Attributes](#attributes)
- [Sub-routing / Group](#group)
- [Middlewares](#middlewares)
  - [Global middlewares](#global-middlewares)
  - [Group based middlewares](#group-middlewares)
  - [Method based middlewares](#method-middlewares)
- [Meta Information](#meta)
  - [State](#state)
  - [Series](#series)
  - [Flag](#flag)
  - [Usages of Meta Information](#meta-usages)
  - [Make your own attributes](#make-attributes)
- [DI Method Injection](#method-injection)
- [Utilities](#utilities)
  - [Route Model](#route-model)
  - [PHPLeague Transformer](#transformer)
  - [Renderer](#renderer)
- [Console Commands](#console)
- [Todos](#todos)
- [Drawbacks](#drawbacks)
- [Feedbacks](#feedbacks)
- [Why](#why)
- [License](#license)

## <a name='features'></a> Features
- Couple your routing with the controller class
- PHP 8 attributes based routing component
- Nested routing
- Provide a flexible ways to control/design your application through means like :
  - sub-routing based middleware
  - meta information
  - create your own attributes, and control it through your own middleware

## <a name='requirements'></a> Requirements
- Laravel
- PHP >= 8
- this packages overrides your laravel Http Kernel and wholly use routing/controller/middleware component from `rosengate/exedra`, 
  however it still fallbacks to laravel routes when there's no matching routes.

## <a name='setup'></a> Setup
#### 1. Install package through composer
For Laravel 10 and below
```
composer require rosengate/sigil
```
For laravel 11 onwards
```
composer require psr/http-message ^1.1
composer require rosengate/sigil
```

#### 2. Register `Sigil\Providers\SigilProvider`
Register `Sigil\Providers\SigilProvider` inside your `bootstrap/providers.php`
```php
    /*
     * Package Service Providers...
     */
    SigilProvider::class
```

#### 3. publish and cache the config
Publish initial sigil.php files, and all the controllers, middlewares, and attributes.
```
php artisan vendor:publish --provider=Sigil\Providers\SigilProvider
```
Config cache after publishing the providers
```
php artisan config:cache
```


#### 4. Http Kernel Extension
Since this package interact changes at http level, sigil does it's own bridging through Laravel Http Kernel.

For laravel 10 and below, extend your `App\Http\Kernel` with `Sigil\SigilKernel` (as this package uses it's own routing and request dispatch).

```php
<?php
namespace App\Http;

class Kernel extends \Sigil\SigilKernel {
}
```

For Laravel 11 onwards, you may go to your `bootstrap/app.php` and replace `Application` with `Sigil\SigilApplication`. This replacement handles Kernel extension on it's own.

## <a name='usages'></a> Basic Usages
Provided with your installation is the root controller where you'd define your initial routing.

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

The second controller `WebController` would be the front facing controller for your app (following laravel similar routing)
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

## <a name='conventions'></a> Routing Conventions
The routing registry through the controller is built upon conventions and prefix through method name.

#### Create an action only for particular (REST) methods
- `get()`, `post()`, `delete()`, `patch()`, `put()`
- can also suffix with additional string for eg. `getUsers()`

<details>
  <summary>Examples</summary>
  
  ```php
  <?php
  namespace App\Http\Controllers;
 
  use Exedra\Routeller\Attributes\Path;

  
  #[Path('/')]
  class WebController extends \Sigil\Controller
  {
      #[Path('/contact-us')]
      public function getContactUs()
      {
      }
      
      #[Path('/contact-us')]
      public function postContactUs()
      {
      }
  }
  ```
</details>

#### Create an action for any methods
- prefix with `execute` WITH additional string
  - for eg. `executeContactUs()`

<details>
  <summary>Examples</summary>
  
  ```php
  <?php
  namespace App\Http\Controllers;
 
  use Exedra\Routeller\Attributes\Path;

  
  #[Path('/')]
  class WebController extends \Sigil\Controller
  {
      #[Path('/about-us')]
      public function executeAboutUs()
      {
      }
  }
  ```
</details>

#### Create a method based middleware
- `middleware` or `middlewareAuth`

[Examples](#method-middlewares)

#### Create a routing group
Nest a routing
- prefix with `group` WITH additional string
  - for eg. `groupBook`

[Examples](#group)
    
#### Routing setup
- if you prefer a more programmatically routing, you can create a `setup(Group $router)` method.

## <a name='attributes'></a> Routing Attributes
- `Path(string path)` define the path for the current route / routing group (relatively)
- `Method(string method|array methods)` set method accessibility
- `Name(string name)` set route name
- `Tag(string tag)` tag the current route
- `Middleware(string middlewareClass)` add middleware
- `State(string key, mixed value)` a mutable meta information
- `Series(string key, mixed value)` an additive meta information
- `Flag(mixed flag)` an array of meta information
- `Requestable(bool)` set whether this route is requestable or not
- `AsFailRoute` mark the selected route as fallback route

## <a name='group'></a> Sub-routing / Group
This package allows you nest your routing beneath another routing indefinitely. Your routing uri/path is relative as it goes down the depth.

Create a method with the name prefixed with `group`, and return the name of the controller.

```php
<?php
use Exedra\Routeller\Attributes\Path;

class RootController extends \Sigil\Controller
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
class AdminController extends \Sigil\Controller
{
    #[Path('/dashboard')]
    public function getDashboard()
    {
    }
}

#[Path('/')]
class WebController extends \Sigil\Controller
{
    public function groupEnquiries()
    {
        return EnquiriesController::class;
    }
}

#[Path('/enquiries')]
class EnquiriesController extends \Sigil\Controller
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

The routing will give a result like :
```
GET  /admin/dashboard
GET  /enquries/form
POST /enquries/form
```

## <a name='middlewares'></a> Middlewares
Feel free to use your laravel middlewares at it still follows the same signature, and the constructor arguments are also injected with laravel di container.

### <a name='global-middlewares'></a> Global middlewares
If you follow the `SigilSetup` above (by providing the array of middleware classes), you'll just need to maintain your list of middleware
in your `App\Http\Kernel` `$middleware` property.

### <a name='group-middlewares'></a> Group/route based middlewares
A class based middlewares.

```php
<?php
namespace App\Http\Controllers;

use Exedra\Routeller\Attributes\Path;
use Exedra\Routeller\Attributes\Middleware;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use App\Http\Middleware\EncryptCookies;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

#[Middleware(EncryptCookies::class)]
#[Middleware(AddQueuedCookiesToResponse::class)]
#[Middleware(StartSession::class)]
class WebController extends \Sigil\Controller
{
    #[Path('/contact-us')]
    #[Middleware(VerifyCsrfToken::class)]
    public function postContactUs()
    {
    }
}
```

### <a name='method-middlewares'></a> Method based middleware
You can make a middleware directly in the controller itself by prefixing the method name with `middleware`.
While doing so, you can also inject any registered instance through the method arguments.

```php
<?php
namespace App\Http\Controllers;

//.. imports

#[Path('/blogs/:blog-id')]
class BlogApiController extends \Sigil\Controller
{
    public function middleware(Request $request, $next, BloggerModel $blogger)
    {
        $blog = BlogModel::findOrFail($request->route('blog-id'));
        
        app()->instance(BlogRepository::class, new BlogRepository($blogger, $blog));
    
        return $next($request);
    }
    
    #[Path('/articles')]
    public function getArticles(BlogRepository $blogService)
    {
        return $blogService->getArticles();
    }
}
```
This method gives you more control over the context of the current routing through the use of middleware.

## <a name='meta'></a> Meta Information
The nested nature of this framework allows us to design our app as flexible as we wish. However, there are three types of 
information we can use for this purpose.

### <a name='state'></a> State
A mutable key based information.

```php
<?php
//...
use Sigil\Context;
use Exedra\Routeller\Attributes\State;
use Exedra\Routeller\Attributes\Path;

#[Path('/')]
#[State('is_ajax', true)]
class WebController extends \Sigil\Controller
{
    #[Path('/contact-us')]
    #[State('is_ajax', false)]
    public function getContactUs(Context $context)
    {
        var_dump($context->getState('is_ajax')); // false
    }
}
```

### <a name='series'></a> Series
An additive / array based key specific information. New information is appended instead of mutated.

```php
<?php
use Sigil\Context;
use Exedra\Routeller\Attributes\Path;
use Exedra\Routeller\Attributes\Series;

#[Series('roles', 'admin')]
class AdminController extends \Sigil\Controller
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

### <a name='flag'></a> Flag
An array of flags / information. Similiar to series, but more simpler.

```php
<?php
use Exedra\Routeller\Attributes\Flag;
use Exedra\Routeller\Attributes\Path;
use Sigil\Context;

#[Flag('authenticated')]
class AdminController extends \Sigil\Controller
{
    #[Path('/dashboard')]
    #[Flag('is_beta')]
    public function getDashboard(Context $context)
    {
        var_dump($context->getFlags()); // ['authenticated', 'is_beta']
    }
}
```


### <a name='meta-usages'></a> Usages of Meta Information
Meta information are best used with a middleware where you could control the flow/behaviour/design of your application by your defined metas.

For eg, let's use some of the meta information we wrote above and write some pseudo codes.

```php
use Sigil\Context;
use App\Models\User;

class RootController extends \Sigil\Controller
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

### <a name='make-attributes'></a> Make your own attributes
The simplest way to create your own attribute is by extending these meta information and use them on your own terms.

For eg. we want to have an attribute that determine which routing goes to which user roles.

```php
<?php
namespace App\Attributes;

use Exedra\Routeller\Attributes\Series;

#[\Attribute]
class Role extends Series
{
    public function __construct($role)
    {
        parent::__construct(static::class, $role);
    }
}
```

Create a middleware to utilize this information.

```php
<?php
namespace App\Http\Middleware;
use Sigil\Context;
use \App\Attributes\Role;

class RolesCheckMiddleware
{
    public function handle($request, $next, Context $context)
    {
        if ($roles = $context->getSeries(Role::class)) {
            //.. do a check if user has these roles
        }
        
        return $next($request);
    }
}
```

Then add this middleware in your `App\Http\Kernel`

Now you can use this attribute in any of your controller.

```php
<?php
namespace App\Http\Controllers;

use Exedra\Routeller\Attributes\Path;
use App\Attributes\Role;

#[Path('/accounts')]
#[Role('accountant')]
class ManageAccountsController extends \Sigil\Controller
{
    public function get()
    {
    }
}
```

### <a name='method-injection'></a> DI Method Injection
The DI wiring of this package make use of laravel container registry. So, anything that you registered on app() container can also be retrieved here.

For eg :

```php
<?php
use Exedra\Routeller\Attributes\Path;

#[Path('/books/:book-id')]
class BookApiController extends \Sigil\Controller
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

## <a name='utilities'></a> Utilities
### <a name='route-model'></a> Route-Model finder / registry
#### Example usages
```php
<?php
use Exedra\Routeller\Attributes\Path;
use Sigil\Utilities\Attributes\Model;
use App\Models\AuthorModel;

#[Path('/authors/:author-id')]
#[Model(AuthorModel::class, 'author-id')]
class AuthorApiController extends \Sigil\Controller
{
    public function get(AuthorModel $author)
    {
        return $author;
    }
}
```

##### Handling exception
You can handle model not found exception by simply creating a middleware that catch such exception.

For eg.

```php
<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class RootController extends \Sigil\Controller
{
    public function middleware(Request $request, $next)
    {
        try {
            return $next($request);
        } catch (ModelNotFoundException $e) {
            //.. do something
        }
    }
}
```

### <a name='transformer'></a> PHPLeague Transformer
PHP League Fractal transformer. Transform your api response from your laravel model/collection 
by annotating your action with .
This package uses `spatie/laravel-fractal`.


#### Usage
```php
<?php
use Exedra\Routeller\Attributes\Path;
use Sigil\Utilities\Attributes\Model;
use App\Models\OrderModels;
use App\Transformers\OrderTransformer;

#[Path('/orders/:order-id')]
#[Model(OrderModel::class, 'order-id')]
class OrderApiController extends \Sigil\Controller
{
    #[Transform(OrderTransformer::class)]
    public function get(OrderModel $order)
    {
        return $order;
    }
}
```

### <a name='renderer'></a> Renderer
Handle the content returns of your controller action by defining a renderer that implements `Sigil\Contracts\Renderer`.

#### Setup



## <a name='console'></a> Console Commands
#### List routes
List all routes
```
php artisan sigil:routes
```

Filter routes under `web.` routing
```
php artisan sigil:routes --name=web
```

## <a name='todos'></a> Todos
- ~~laravel url generator compatibility~~
- ~~better installation / setup procedure~~
- related artisan commands
  - ~~route list~~
  - make controller
- Caching strategy / testing
- More stable release

## <a name='drawbacks'></a> Drawbacks
~~As this package completely use a different component for routing, in general it will be incompatible with any other packages 
that make use of laravel routing or the `routes` folder. Also these components as of now :~~
~~- Url Generator~~
~~- Redirection with route name~~
As of now, this package will still fallback to laravel routing when there're no matching routes.

## <a name='why'></a> Why
I wrote `rosengate/exedra` back 4 years ago because i couldn't find a framework that can exactly do what I wanted, like hierarchically nest a routing beneath another routing. 
Also `exedra` was never meant to be another full-fledged framework. It's just a microframework and I always advocate for the use of tons of amazing php packages out there. 
Then I built a phpdoc based routing controller component and since then writing a code with `exedra` became a bliss than ever. 
But building things from microframework can be daunting as I always needed an ORM, validation, error handling, and many other tools out there (I always find myself using Elqouent).

Then at one point I became so used with laravel and decided to try it with exedra. I was starting to think that this is kinda possible. 
Then PHP8 came with a news so good I've been waiting for years. Attributes/Annotation. So I decided to just port it for laravel and see how it goes here. <3

## <a name='feedbacks'></a> Feedbacks
- Feel free to throw in feedbacks through github issues.
~~- I am planning to find a way to integrate with `Illuminate\Contracts\Routing\UrlGenerator` soon.~~

## <a name='license'></a> License
[MIT License](LICENSE)
