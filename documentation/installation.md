## Installation/Setup
1. `composer require fuzz/magic-box`
1. Use or extend `Fuzz\MagicBox\Middleware\RepositoryMiddleware` into your project and register your class under the `$routeMiddleware` array in `app/Http/Kernel.php`. `RepositoryMiddleware` contains a variety of configuration options that can be overridden
1. If you're using `fuzz/api-server`, you can use magical routing by updating `app/Providers/RouteServiceProvider.php`, `RouteServiceProvider@map`, to include:

	```php
    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router $router
     * @return void
     */
    public function map(Router $router)
    {
        // Register a handy macro for registering resource routes
        $router->macro('restful', function ($model_name, $resource_controller = 'ResourceController') use ($router) {
            $alias = Str::lower(Str::snake(Str::plural(class_basename($model_name)), '-'));

            $router->resource($alias, $resource_controller, [
                'only' => [
                    'index',
                    'store',
                    'show',
                    'update',
                    'destroy',
                ],
            ]);
        });

        $router->group(['namespace' => $this->namespace], function ($router) {
            require app_path('Http/routes.php');
        });
    }
	```
1. Set up your MagicBox resource routes under the middleware key you assign to your chosen `RepositoryMiddleware` class
1. Set up a `YourAppNamespace\Http\Controllers\ResourceController`, [here is what a ResourceController might look like](https://gist.github.com/SimantovYousoufov/dea19adb1dfd8f05c1fcad9db976c247) .
1. Set up models according to `Model Setup` section

## Testing
Just run `phpunit` after you `composer install`.
