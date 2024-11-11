<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Tests\Fixtures;

use Generator;
use Membrane\OpenAPIRouter\RouteCollection;

final class ProvidesTrainTravel
{
    public static function getFilePath(): string
    {
        return __DIR__ . '/train-travel-api.yaml';
    }

    public static function getRoutes(): RouteCollection
    {
        return new RouteCollection([
            'hosted' => [
                'static' => ['https://api.example.com' => [
                    'static' => [
                        '/stations' => ['get' => 'get-stations'],
                        '/trips' => ['get' => 'get-trips'],
                        '/bookings' => ['get' => 'get-bookings', 'post' => 'create-booking'],
                    ],
                    'dynamic' => [
                        'regex' => '#^(?|/bookings/([^/]+)(*MARK:/bookings/{bookingId})|/bookings/([^/]+)/payment(*MARK:/bookings/{bookingId}/payment))$#',
                        'paths' => [
                            '/bookings/{bookingId}' => ['get' => 'get-booking', 'delete' => 'delete-booking'],
                            '/bookings/{bookingId}/payment' => ['post' => 'create-booking-payment'],
                        ],
                    ],
                ]],
                'dynamic' => ['regex' => '#^(?|)#', 'servers' => []],
            ],
            'hostless' => ['static' => [], 'dynamic' => ['regex' => '#^(?|)#', 'servers' => []]],
        ]);
    }

    public static function getRoutesIgnoringServers(): RouteCollection
    {
        return  new RouteCollection([
            'hosted' => ['static' => [], 'dynamic' => ['regex' => '#^(?|)#', 'servers' => []]],
            'hostless' => [
                'static' => ['' => [
                    'static' => [
                        '/stations' => ['get' => 'get-stations'],
                        '/trips' => ['get' => 'get-trips'],
                        '/bookings' => ['get' => 'get-bookings', 'post' => 'create-booking'],
                    ],
                    'dynamic' => [
                        'regex' => '#^(?|/bookings/([^/]+)(*MARK:/bookings/{bookingId})|/bookings/([^/]+)/payment(*MARK:/bookings/{bookingId}/payment))$#',
                        'paths' => [
                            '/bookings/{bookingId}' => ['get' => 'get-booking', 'delete' => 'delete-booking'],
                            '/bookings/{bookingId}/payment' => ['post' => 'create-booking-payment'],
                        ],
                    ],
                ]],
                'dynamic' => ['regex' => '#^(?|)#', 'servers' => []],
            ],
        ]);
    }
}
