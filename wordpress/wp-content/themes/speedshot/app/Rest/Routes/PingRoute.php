<?php

namespace Speedshot\Theme\Rest\Routes;

use Speedshot\Theme\Rest\BaseRoute;
use Speedshot\Theme\Rest\Contracts\RouteInterface;
use WP_REST_Response;

class PingRoute extends BaseRoute implements RouteInterface
{
    public static function register(): void
    {
        self::register_route('/ping', [
            'methods' => 'GET',
            'permission_callback' => '__return_true',
            'callback' => static fn() => new WP_REST_Response([
                'ok' => true,
                'message' => 'pong',
            ], 200),
        ]);
    }
}
