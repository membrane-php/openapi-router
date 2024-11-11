<?php

declare(strict_types=1);

namespace Membrane\OpenAPIRouter\Tests\Fixtures;

use Generator;
use Membrane\OpenAPIReader\Tests\Fixtures\ProvidesTrainTravelApi;
use Membrane\OpenAPIRouter\RouteCollection;

final class ProvidesApiAndRoutes
{
    public static function defaultBehaviour(): Generator
    {
        yield 'petstore-expanded' => [
            ProvidesPetstoreExpanded::getFilePath(),
            ProvidesPetstoreExpanded::getRoutes(),
        ];

        yield 'train-travel' => [
            ProvidesTrainTravel::getFilePath(),
            ProvidesTrainTravel::getRoutes()
        ];

        yield 'APIece of Cake' => [
            ProvidesAPIeceOfCake::getFilePath(),
            ProvidesAPIeceOfCake::getRoutes(),
        ];

        yield 'Weird and Wonderful' => [
            ProvidesWeirdAndWonderful::getFilePath(),
            ProvidesWeirdAndWonderful::getRoutes(),
        ];
    }

    public static function ignoringServers(): Generator
    {
        yield 'petstore-expanded' => [
            ProvidesPetstoreExpanded::getFilePath(),
            ProvidesPetstoreExpanded::getRoutesIgnoringServers(),
        ];

        yield 'train-travel' => [
            ProvidesTrainTravel::getFilePath(),
            ProvidesTrainTravel::getRoutesIgnoringServers()
        ];

        yield 'APIece of Cake' => [
            ProvidesAPIeceOfCake::getFilePath(),
            ProvidesAPIeceOfCake::getRoutesIgnoringServers(),
        ];

        yield 'Weird and Wonderful' => [
            ProvidesWeirdAndWonderful::getFilePath(),
            ProvidesWeirdAndWonderful::getRoutesIgnoringServers(),
        ];
    }
}
