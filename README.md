# Laravel dynamic routes

Laravel dynamic routes is a simple RouteServiceProvider that dynamically resolves routes from route files based on their
placement within the filesystem.
Route names and prefixes are dynamically allocated to the routes resolved by this provider, and middleware (groups) can
be assigned on a directory level.

## Installation
```shell
composer require dannypas00/laravel-dynamic-routes
```

### Implementation

Implementation is very simple; just change the default RouteServiceProvider import in your
project's `App\Providers\RouteServiceProvider` from `Illuminate\Foundation\Support\Providers\RouteServiceProvider`
to `DannyPas00\LaravelDynamicRoutes\RouteServiceProvider`:

```php
<?php

namespace App\Providers;

use DannyPas00\LaravelDynamicRoutes\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/home';
}
```

## Example

Say we have a directory structure like so:

```
- MyProject/
  - app/
  - routes/
    - api/
      - rest/
        - users.php -> index,show
        - shops.php -> index,show
    - web/
      - root.php  -> home
      - users.php -> index,show
```

In vanilla laravel, you would need to add code to the RouteServiceProvider to register each of these files individually.
This made managing large projects cumbersome because you would either end up with a couple very big route files (such as
web.php or api.php), or many small ones that all require a separate piece of code in the RouteServiceProvider,
cluttering the provider instead.
With dynamic routing however, all of these route files will be registered without any additional configuration needed.
When this is done, the routes in the route file take on a path associated with the file location.
E.g. the routes registered in the `routes/api/rest/users.php` file will all be registered as `api/rest/users/{route}`.

The full output of `php artisan route:list` would look something like this:

```
GET|HEAD   api/rest/users ............... api.users.index  
GET|HEAD   api/rest/users/{id} .......... api.users.show  
GET|HEAD   api/rest/shops ............... api.shops.index  
GET|HEAD   api/rest/shops/{id} .......... api.shops.show  
GET|HEAD   users ........................ users.index  
GET|HEAD   users/{id} ................... users.show  
GET|HEAD   / ............................ home  
```

Since the dynamic route provider extends the laravel provider, all methods can still be overwritten, so long as the
dynamic provider's `boot()` or `routeRegistration()` method is called.

## Customizing

The following customization options are available within the route service provider:

### Route directory

Setting the `ROUTE_DIRECTORY` const in the implementing service provider will change the default `routes/` directory.

### Root file

Setting the `ROOT_FILE` const in the implementing service provider will change which route files will be treated as "
root" files.  
Any root file in any directory will not reflect its name into its prefix or path (see root.php in example).

### Flatten directories

Any directory added to the `FLATTEN_DIRECTORIES` array will be ignored when building paths and prefixes.  
Set to just `'web'` by default, any file in the `routes/web` directory will not have "web" in its route or prefix.

### Middleware matching

For any directory-wide middleware matching (web and api split for example), the `matchMiddleware` function can be
overwritten.  
It accepts the route's directory as string, and expects a middleware string (or null when not middleware needs to be
set) as a response.
