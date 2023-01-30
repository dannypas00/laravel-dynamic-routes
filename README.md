# Laravel dynamic routes
Laravel dynamic routes is a simple RouteServiceProvider that dynamically resolves routes from route files based on their placement within the filesystem.
Route names and prefixes are dynamically allocated to the routes resolved by this provider, and middleware (groups) can be assigned on a directory level.
## Example
Say we have a directory structure like so:
```
- MyProject/
  - app/
  - routes/
    - api/
      - rest/
        - users.php
        - shops.php
    - web/
      - users.php
    - console.php
```

In vanilla laravel, you would need to add code to the RouteServiceProvider to register each of these files individually. This made managing large projects cumbersome because you would either end up with a couple very big route files (such as web.php or api.php), or many small ones that all require a separate piece of code in the RouteServiceProvider, cluttering the provider instead.
With dynamic routing however, all of these route files will be registered without any additional configuration needed. When this is done, the routes in the route file take on a path associated with the file location.
E.g. the routes registered in the `routes/api/rest/users.php` file will all be registered as `api/rest/users/{route}`.

## Implementation
Implementation is very simple; just change the default RouteServiceProvider import in your project's `App\Providers\RouteServiceProvider` from `Illuminate\Foundation\Support\Providers\RouteServiceProvider` to `DannyPas00\LaravelDynamicRoutes\RouteServiceProvider`:
```php
<?php

namespace App\Providers;

use DannyPas00\LaravelDynamicRoutes\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/home';
}
```
Since the dynamic route provider extends the laravel provider, all methods can still be overwritten, so long as the dynamic provider's `boot()` method is called.