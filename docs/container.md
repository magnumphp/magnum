# Magnum Container

This is an IoC class that extends Woohoo Labs' [Zen](https://github.com/woohoolabs/zen) container.

The notable extension points are the DependencyResolver which allows the use of `Param` types
to be rendered. These allow you to specify strings, arrays, class method calls (classes must be in the Container),
and static method calls.

## Available Param Types

### ArrayParam

Returns the PHP code needed to render the array.

```php
echo (new ArrayParam(1,2));      // array(1, 2)
echo (new ArrayParam('test',2)); // array('test', 2)
``` 

### ClassMethodParam

Returns the PHP code needed to call a method on a class within the container

```php
echo (new ClassMethodParam('Test', 'testing')); // ($this->singletonEntry['Test'] ?? $this->Test())->testing() 
echo (new ClassMethodParam('Test', 'testing', 1, 2)); // ($this->singletonEntry['Test'] ?? $this->Test())->testing(1, 2) 
```

### StaticMethodParam

Returns the PHP code needed to call a static method on a class within the container. This is useful for
certain 

```php
echo (new StaticMethodParam('Test', 'testing')); // \Test::testing()
echo (new StaticMethodParam('Test', 'testing', 1, 2)); // \Test::testing(1, 2)
```

### StringParam

Return the PHP coded needed to render the string.

```php
echo (new StringParam('test')); // 'test'
```