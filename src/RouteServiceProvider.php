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
     * Routes in this files will not be prefixed
     * Leaving this as 'root' will prevent any 'root.php' file in any directory from having its name prefixed
     */
    public const ROOT_FILE = 'root';

    public const FLATTEN_DIRECTORIES = ['web'];

    /**
     * Register all routes in the ROUTE_DIRECTORY directory
     */
    public function boot(): void
    {
        $this->routes(function (): void {
            $this->routeRegistration();
        });
    }

    /**
     * Inherit this function to specify directory-wide middleware
     * e.g. /routes/api/ will use the 'api' middleware
     *
     * @param string $directory
     * @return string|null
     */
    protected function matchMiddleware(string $directory): string|null
    {
        return null;
    }

    /**
     * Function that actually registers the routes
     * Be sure to call this if you implement your own routes in the $this->routes() function since it can only be called once
     */
    protected function routeRegistration(): void
    {
        $this->registerDirectory(base_path(self::ROUTE_DIRECTORY));
    }

    /**
     * Recursively register a whole directory
     *
     * @param string $directory
     */
    private function registerDirectory(string $directory): void
    {
        collect(File::files($directory))->each(function (SplFileInfo $fileInfo) use ($directory): void {
            $this->registerRouteFile($directory, $fileInfo);
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
        $directory = trim($directory, '/');

        $middlewareName = $this->matchMiddleware($directory);

        // Flatten directory if it starts with a flattened directory
        // Note: This will break in an edge case where a flatten-directory is re-used in an already flattened tree
        // e.g. if FLATTEN_DIRECTORIES contains 'web', and there is a file registered at /routes/web/foo/web/bar.php
        if (Str::startsWith($directory, self::FLATTEN_DIRECTORIES)) {
            $directory = Str::remove(self::FLATTEN_DIRECTORIES, $directory);
        }

        $prefix = $this->getPrefix($directory, $fileInfo);
        $dottedRoute = str_replace(DIRECTORY_SEPARATOR, '.', $prefix);

        $routeRegistrar = Route::name(ltrim($dottedRoute . '.', '.'));

        if ($middlewareName) {
            $routeRegistrar->middleware($middlewareName);
        }

        $routeRegistrar->prefix($prefix)
            ->namespace($this->namespace)
            ->group($fileInfo->getPathname());
    }

    /**
     * If the filename corresponds to the ROOT_FILE, don't add its name to the route
     *
     * @param string $directory
     * @param SplFileInfo $fileInfo
     * @return string
     */
    private function getPrefix(string $directory, SplFileInfo $fileInfo): string
    {
        $baseName = $fileInfo->getBasename('.' . $fileInfo->getExtension());
        if ($baseName === self::ROOT_FILE) {
            return $directory;
        }
        return $directory . '/' . $baseName;
    }
}
