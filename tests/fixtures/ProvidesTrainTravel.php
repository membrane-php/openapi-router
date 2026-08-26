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
                        '/stations' => ['get' => ['operationId' => 'get-stations', 'source' => '']],
                        '/trips' => ['get' => ['operationId' => 'get-trips', 'source' => '']],
                        '/bookings' => [
                            'get' => ['operationId' => 'get-bookings', 'source' => ''],
                            'post' => ['operationId' => 'create-booking', 'source' => ''],
                        ],
                    ],
                    'dynamic' => [
                        'regex' => '#^(?|/bookings/([^/]+)(*MARK:/bookings/{bookingId})|/bookings/([^/]+)/payment(*MARK:/bookings/{bookingId}/payment))$#',
                        'paths' => [
                            '/bookings/{bookingId}' => [
                                'get' => ['operationId' => 'get-booking', 'source' => ''],
                                'delete' => ['operationId' => 'delete-booking', 'source' => ''],
                            ],
                            '/bookings/{bookingId}/payment' => [
                                'post' => ['operationId' => 'create-booking-payment', 'source' => ''],
                            ],
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
                        '/stations' => ['get' => ['operationId' => 'get-stations', 'source' => '']],
                        '/trips' => ['get' => ['operationId' => 'get-trips', 'source' => '']],
                        '/bookings' => [
                            'get' => ['operationId' => 'get-bookings', 'source' => ''],
                            'post' => ['operationId' => 'create-booking', 'source' => '']
                        ],
                    ],
                    'dynamic' => [
                        'regex' => '#^(?|/bookings/([^/]+)(*MARK:/bookings/{bookingId})|/bookings/([^/]+)/payment(*MARK:/bookings/{bookingId}/payment))$#',
                        'paths' => [
                            '/bookings/{bookingId}' => [
                                'get' => ['operationId' => 'get-booking', 'source' => ''],
                                'delete' => ['operationId' => 'delete-booking', 'source' => '']
                            ],
                            '/bookings/{bookingId}/payment' => [
                                'post' => ['operationId' => 'create-booking-payment', 'source' => '']
                            ],
                        ],
                    ],
                ]],
                'dynamic' => ['regex' => '#^(?|)#', 'servers' => []],
            ],
        ]);
    }
}
