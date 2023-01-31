<?php

declare(strict_types=1);

namespace DannyPas00\LaravelDynamicRoutes;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use SplFileInfo;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * What directory to use for the routes
     * Will almost always stay default 'routes'
     */
    public const ROUTE_DIRECTORY = 'routes';

    /**
     * Register all routes in the ROUTE_DIRECTORY directory
     */
    public function boot(): void
    {
        $this->routes(function (): void {
            // Load all route directories
            collect(File::directories(base_path(self::ROUTE_DIRECTORY)))->each(function (string $directory): void {
                // Load all route files in each directory
                $this->registerDirectory($directory);
            });
        });
    }

    /**
     * Recursively register a whole directory
     *
     * @param string $directory
     */
    private function registerDirectory(string $directory): void
    {
        collect(File::allFiles($directory))->each(function (SplFileInfo $fileInfo) use ($directory): void {
            $this->registerRouteFile(trim(DIRECTORY_SEPARATOR, $directory), $fileInfo);
        });
        collect(File::directories($directory))->each(function (string $directory): void {
            $this->registerDirectory($directory);
        });
    }

    /**
     * Register a single route file
     *
     * @param string $directory
     * @param SplFileInfo $fileInfo
     */
    private function registerRouteFile(string $directory, SplFileInfo $fileInfo): void
    {
        $directory = Str::remove(base_path(self::ROUTE_DIRECTORY), $directory);
        $dottedDirectory = str_replace(DIRECTORY_SEPARATOR, '.', $directory);
        $route = $directory . '/' . $fileInfo->getBasename('.' . $fileInfo->getExtension());
        $dottedRoute = str_replace(DIRECTORY_SEPARATOR, '.', $route);

        $routeRegistrar = Route::name($dottedRoute . '.');

        if ($middlewareName = $this->matchMiddleware($dottedDirectory)) {
            $routeRegistrar->middleware($middlewareName);
        }

        $routeRegistrar->prefix($route)
            ->namespace($this->namespace)
            ->group($fileInfo->getPathname());
    }

    /**
     * Inherit this function to specify directory-wide middleware
     * e.g. /routes/api/ will use the 'api' middleware
     *
     * @param string $directory
     * @return string|null
     */
    private function matchMiddleware(string $directory): string|null
    {
        return match ($directory) {
            default => null
        };
    }
}
