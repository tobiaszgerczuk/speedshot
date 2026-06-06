<?php

namespace Speedshot\Theme\Rest;

use Speedshot\Theme\Rest\Contracts\RouteInterface;
use Speedshot\Theme\Rest\Routes\CartRoute;
use Speedshot\Theme\Rest\Routes\PingRoute;
use Speedshot\Theme\Rest\Routes\PostsRoute;

class Api
{
    public static function boot(): void
    {
        add_action('rest_api_init', [self::class, 'register_routes']);
    }

    public static function register_routes(): void
    {
        foreach (self::route_classes() as $routeClass) {
            if (!is_a($routeClass, RouteInterface::class, true)) {
                continue;
            }

            $routeClass::register();
        }
    }

    /**
     * Central list of route classes.
     * Add new endpoint classes here.
     *
     * @return array<class-string<RouteInterface>>
     */
    private static function route_classes(): array
    {
        return [
            PingRoute::class,
            CartRoute::class,
            PostsRoute::class,
        ];
    }
}
