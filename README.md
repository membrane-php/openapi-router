# OpenAPI Router

This library routes HTTP requests to operationIds in your OpenAPI specification.
To make sure it runs quickly we've used techniques inspired
by [Nikita Popov](https://www.npopov.com/2014/02/18/Fast-request-routing-using-regular-expressions.html)
and [Nicolas Grekas](https://nicolas-grekas.medium.com/making-symfonys-router-77-7x-faster-1-2-958e3754f0e1).

## Requirements

- A valid [OpenAPI specification](https://github.com/OAI/OpenAPI-Specification#readme).
- An operationId on all [Operation Objects](https://spec.openapis.org/oas/v3.1.0#operation-object) so that each route is uniquely identifiable.

## Rules

### Naming Conventions

- Forward slashes at the end of a server url will be ignored since [paths MUST begin with a forward slash.](https://spec.openapis.org/oas/v3.1.0#paths-object)
- [Dynamic paths which are identical other than the variable names MUST NOT exist.](https://spec.openapis.org/oas/v3.1.0#paths-object)

### Routing Priorities

- [Static urls MUST be prioritized over dynamic urls](https://spec.openapis.org/oas/v3.1.0#paths-object).
- Longer urls are prioritized over shorter urls.
- Hosted servers will be prioritized over hostless servers.

## Installation

```text
composer require membrane/openapi-router
```

## Quick Start

To read routes dynamically, you can do the following:

```php
<?php

use Membrane\OpenAPIRouter\Reader\OpenAPIFileReader;use Membrane\OpenAPIRouter\RouteCollector;use Membrane\OpenAPIRouter\Router;

$openApi = (new OpenAPIFileReader())->readFromAbsoluteFilePath('/app/petstore.yaml');
$routeCollection = (new RouteCollector())->collect($openApi);

$router = new Router($routeCollection);
$requestedOperationId = $router->route('http://petstore.swagger.io/v1/pets', 'get');

echo $requestedOperationId; // listPets
```

## Caching Routes

Run the following console command to cache the routes from your OpenAPI, to avoid reading your OpenAPI file everytime:

```text
membrane:router:generate-routes <openapi-filepath> <destination-filepath>
```

```php
<?php

use Membrane\OpenAPIRouter\Router;

$routeCollection = include '/app/cache/routes.php';

$router = new Router($routeCollection);
$requestedOperationId = $router->route('http://petstore.swagger.io/v1/pets', 'get');

echo $requestedOperationId; // listPets
```
