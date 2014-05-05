# Why Dependency Injection

Dependency Injection (DI) can be a somewhat complicated concept to those who aren't familiar with it. Once you see it and get used to it the benefits become clear, so let's go over an example:

```php
class Samurai
{
	private $sword;
	private $shuriken;

	public function __construct()
	{
		$this->sword = new Sword;
		$this->shuriken = new Shuriken;
	}

	public function attack($useSword = true)
	{
		if ($useSword)
		{
			$this->sword->hit();
		}
		else
		{
			$this->shuriken->hit();
		}
	}
}
```
```php
class Sword
{
	public function hit($target)
	{
		echo 'Hit the ' . $target;
	}
}
```
```php
class Shuriken
{
	public function hit($target)
	{
		echo 'Throw shuriken at ' . $target;
	}
}
```
```php
$warrior = new Samurai;

// preparations....

$warrior->attack();
```

### The Situation

In the last code block above, imagine yourself as the commander of a samurai army. You are aware of the battle and what needs to be done to win, and as such, you are preparing your attack. So, you tell one of your warriors to prepare themselves for battle. As he's preparing, he has to stop and locate his weapons before he is ready to attack. Then, he stands idle waiting for your command, which you issue. He runs out sword drawn, but then you realize it would be better to use a bow and arrow instead. But your warrior didn't know to bring his bow with him. The battle is lost because of poor preparation.

### The Problem

Since your told your warrior to prepare himself for battle, he took the items he was familiar with and prepared the best he could. There's no way he could carry every possible weapon you might request of him. Instead of letting the samurai dictate what weapons to use, it would obviously be better for you to provide the weapons for him.

### The Solution

The best way to solve this is to provide the weapon for him as he's preparing. There is one major task you need to do in order to train the samurai on how to use any weapon you might throw at him, but it's worth the effort.

#### Create an Interface

An interface is a contract between the implementing class and the calling class that certain criteria will be met by each class that implements the interface. We currently have 2 weapons, let's make a contract for them, and then implement that contract in the classes so the samurai is properly trained.

```php
interface WeaponInterface
{
	public function hit($target);
}

class Sword implements WeaponInterface
{
	public function hit($target)
	{
		echo 'Hit ' . $target;
	}
}

class Shuriken implements WeaponInterface
{
	public function hit($target)
	{
		echo 'Throw shuriken at ' . $target;
	}
}
```

Now that we know our weapons will have a hit method, and since they signed the contract by implementing the interface, we can easily modify our samurai to receive one of these weapons while he's preparing.

```php
class Samurai
{
	protected $weapon;

	public function __construct(WeaponInterface $weapon)
	{
		$this->weapon = $weapon;
	}

	public function setWeapon(WeaponInterface $weapon)
	{
		$this->weapon = $weapon;
	}

	public function attack($target)
	{
		$this->weapon->hit($target);
	}
}
```

As you can see, we've greatly reduced the amount of preparation work he needs to do.

```php
$warrior = new Samurai(new Sword);

$warrior->attack('the enemy');
```

That's the basics of DI. Passing the requirements for a class to the class via it's constructor or via a `setProperty` method, where "property" would typically match the name of the property you are setting, as in the second version of the Samurai class with the `setWeapon` method. Here's an example using the setter for DI.

```php
$warrior = new Samurai;

$warrior->setWeapon(new Sword);

$warrior->attack();
```

## How A Can Container Help

An Inversion of Control (IoC) Container can help you to manage all the parts of the application. Instead of re-building a new warrior each time, it would be much easier for the app to remember how to prepare a warrior and be able to create one on demand. In our example, since the Samurai doesn't have a lot of dependencies, the benefits of a container might be hard to see. But consider that each time you want to create a warrior you need to remember to pass in the dependencies. With a container, you can set up a template, so to speak, and let the app handle the creation. It REALLY comes in handy when the dependencies you are injecting have dependencies within their dependencies. It can get very complicated very fast.

```php
$warrior = new Samurai(new Sword);
$warrior->attack();

$warrior = new Samurai(new Sword);
$warrior->attack();

$warrior = new Samurai(new Sword);
$warrior->attack();

$warrior = new Samurai(new Sword);
$warrior->attack();

// vs

$ioc['warrior']->attack();
$ioc['warrior']->attack();
$ioc['warrior']->attack();
$ioc['warrior']->attack();

/**
 * This would be in your app bootstrap
 * or somewhere out of the way.
 */
$ioc = new Joomla\DI\Container;

$ioc->set('warrior', function ()
{
	return new Samurai(new Sword);
}, false);
```

```
Note: The samurai concept came from a DI/IoC video about using Ninject in .NET. I've expanded upon the concept here.
```