# OpenAPI Router

# Rules

- forward slash on the end of a server will be ignored, paths must begin with a forward slash according to openapi spec, so server must not end with one.
- all paths must have an operationId in order for the routing to work
- server matches are prioritized by length
- hosted server matches are prioritized
- static servers and static paths are prioritized (following openapi spec)
- dynamic paths which are identical other than the variable name are invalid (following openapi spec)
- 
