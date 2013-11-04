# The DI Package

The Dependency Injection package for Joomla provides a simple IoC Container for your application. Dependency Injection allows you the developer to control the construction and lifecycle of your objects, rather than leaving that control to the classes themselves. Instead of hard coding a class's dependencies within the class `__construct()` method, you instead provide to a class the dependencies it requires as arguments to its constructor. This helps to decrease hard dependencies and to create loosely coupled code.

Read more about [why you should be using dependency injection](docs/why-dependency-injection.md).

An Inversion of Control (IoC) Container helps you to manage these dependencies in a controlled fashion.

## Automatic Dependency Resolution

The DI Container is able to recursively resolve objects and their dependencies. It does this by inspecting the type hints on the object's constructor. As such, this method of resolution has a small limitation; you are limited to constructor injection. There is no support for setter injection.

```php
include 'Container.php';

class Foo
{
    public $bar;
    public $baz;

    public function __construct(Bar $bar, Baz $baz)
    {
        $this->bar = $bar;
        $this->baz = $baz;
    }
}

class Bar
{
    public $qux;

    public function __construct(Qux $qux)
    {
        $this->qux = $qux;
    }
}

class Baz {}

class Qux {}

$container = new Joomla\DI\Container;

var_dump($container['Foo']);
```
Running the above will give you the following result:

```
class Foo#5 (2) {
  public $bar =>
  class Bar#9 (1) {
    public $qux =>
    class Qux#13 (0) {
    }
  }
  public $baz =>
  class Baz#14 (0) {
  }
}

```
