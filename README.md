# OpenAPI Router

This library will route http requests to operationIds in your OpenAPI specification.
To make sure it runs quickly we've used techniques inspired
by [Nikita Popov](https://www.npopov.com/2014/02/18/Fast-request-routing-using-regular-expressions.html)
and [Nicolas Grekas](https://nicolas-grekas.medium.com/making-symfonys-router-77-7x-faster-1-2-958e3754f0e1)

# Requirements

- A valid OpenAPI specification. This is required in order to collect all available routes.
- All operations in your OpenAPI MUST contain an operationId. When routing an incoming request, the operationId is what
  the router will return.

# Rules

## Naming Conventions

- Forward slashes at the end of a server url will be ignored in order to comply with the OpenAPI Specification.
    - [Paths MUST begin with a forward slash](https://spec.openapis.org/oas/v3.1.0#paths-object), as such valid urls can
      only be created if the server does not end with one.
- [Dynamic paths which are identical other than the variable names MUST NOT exist.](https://spec.openapis.org/oas/v3.1.0#paths-object)

## Routing Priorities

- Static urls will be prioritized over dynamic urls
  to [(Required by OpenAPI Specification)](https://spec.openapis.org/oas/v3.1.0#paths-object)
- Longer urls are prioritized over shorter urls
- Hosted servers will be prioritized over hostless servers
