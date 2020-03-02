# Magnum Container

This is an IoC class that extends the [Symfony DependencyInjection Component](https://symfony.com/doc/current/components/dependency_injection.html).

## Builder

This extends the Symfony ContainerBuilder, but overrides some of the default handling

### Available Methods

- `reference($class)` Generates a reference to the class if exists. If not then the class will be registered
- `proxy($alias, $class)` Adds a proxy to the given class 
- `alias($target, $id)` Register's an id as a single instance (shared among calls)
- `factory($id, $class, $method)` Creates a Factory definition in the container
- `get($id)` Returns the Symfony DI Definition
- `instance($id, $class)` Register's an id as shared instance (new on every call)
- `register($id, $class)` Register's an id as a single instance (shared among calls)
- `decorate($parent, $child)` Registers the child in place of  the parent, and makes the parent the first argument of the child
- `singleton($id, $class)` Alias to `register($id, $class)`
- `modifier($id, $modifier)` Set a callable to be run before compiling

### Parameters

- `hasParameter($id)` Returns whether or not the parameter exists
- `getParameter($id, $default)` Returns the value of the given param
- `setParameter($id, $value)` Sets the value of the given parameter
- `setParameters($params)` Merges the parameters in to the existing parameters
- `setParameterDefault($id, $value)` Sets the value of the parameter if not defined (is used at container compile time)

### Extended operations

- `addCompilerPass(...)` Proxies to the Symfony Container addCompilerPass() method.
- `findClassesInPath($path)` Returns an array of any classes in *.php files

## Loader

Handles loading the container from the cache or building it from the providers

## Compiler Passes

###FullAutowirePass

This Compiler pass attempts to load parameters from the container first, then uses the Reflection API to attempt to find the correct substitutions.

### ResolveDefaultParameters

By using `$builder->setParameterDefault($key, $value)` then during the compiler phase, if the value hasn't been previously set, the defaults will be used.

An easy exampel would be if you have `__construct($path)`, then setting the default `$path` will result in this being prefilled if you don't specify it during the DI setup.
