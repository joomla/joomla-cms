# The Registry Package [![Build Status](https://travis-ci.org/joomla-framework/registry.png?branch=master)](https://travis-ci.org/joomla-framework/registry)

``` php
use Joomla\Registry\Registry;

$registry = new Registry;

// Set a value in the registry.
$registry->set('foo') = 'bar';

// Get a value from the registry;
$value = $registry->get('foo');

```

## Load config by Registry

``` php
use Joomla\Registry\Registry;

$registry = new Registry;

// Load by string
$registry->loadString('{"foo" : "bar"}');

$registry->loadString('<root></root>', 'xml');

// Load by object or array
$registry->loadObject($object);
$registry->loadArray($array);

// Load by file
$registry->loadFile($root . '/config/config.json', 'json');
```

## Accessing a Registry by getter & setter

### Get value

``` php
$registry->get('foo');

// Get a non-exists value and return default
$registry->get('foo', 'default');

// OR

$registry->get('foo') ?: 'default';
```

### Set value

``` php
// Set value
$registry->set('bar', $value);

// Sets a default value if not already assigned.
$registry->def('bar', $default);
```

### Accessing children value by path

``` php
$json = '{
	"parent" : {
		"child" : "Foo"
	}
}';

$registry = new Registry($json);

$registry->get('parent.child'); // return 'Foo'

$registry->set('parent.child', $value);
```

## Accessing a Registry as an Array

The `Registry` class implements `ArrayAccess` so the properties of the registry can be accessed as an array. Consider the following examples:

``` php
// Set a value in the registry.
$registry['foo'] = 'bar';

// Get a value from the registry;
$value = $registry['foo'];

// Check if a key in the registry is set.
if (isset($registry['foo']))
{
	echo 'Say bar.';
}
```

## Merge Registry

#### Using load* methods to merge two config files.

``` php
$json1 = '{
    "field" : {
        "keyA" : "valueA",
        "keyB" : "valueB"
    }
}';

$json2 = '{
    "field" : {
        "keyB" : "a new valueB"
    }
}';

$registry->loadString($json1);
$registry->loadString($json2);
```

Output

```
Array(
    field => Array(
        keyA => valueA
        keyB => a new valueB
    )
)
```

#### Merge another Registry

``` php
$object1 = '{
	"foo" : "foo value",
	"bar" : {
		"bar1" : "bar value 1",
		"bar2" : "bar value 2"
	}
}';

$object2 = '{
	"foo" : "foo value",
	"bar" : {
		"bar2" : "new bar value 2"
	}
}';

$registry1 = new Registry(json_decode($object1));
$registry2 = new Registry(json_decode($object2));

$registry1->merge($registry2);
```

If you just want to merge first level, do not hope recursive:

``` php
$registry1->merge($registry2, false); // Set param 2 to false that Registry will only merge first level
```

## Dump to file.

``` php
$registry->toString();

$registry->toString('xml');

$registry->toString('ini');
```

## Using YAML

Add Symfony YAML component in `composer.json`

``` json
{
	"require-dev": {
		"symfony/yaml": "~2.0"
	}
}
```

Using `yaml` format

``` php
$registry->loadFile($yamlFile, 'yaml');

$registry->loadString('foo: bar', 'yaml');

// Convert to string
$registry->toString('yaml');
```

## Using XML

Keep in mind that due to XML complexity, special format must be kept when loading into Registry.
By default, the parent XML element should be named "registry" and all child elements should be named "node".
The nodes should include a "name" attribute, for the name of the value. The nodes can be optionally filtered with a "type"
attribute. Valid types are:

* array
* boolean
* double
* integer
* object (default)
* string

**Loading input**

``` xml
<?xml version="1.0"?>
<registry>
	<node name="foo_1" type="string">bar</node>
	<node name="foo_2" type="boolean">1</node>
	<node name="foo_3" type="integer">42</node>
	<node name="foo_4" type="double">3.1415</node>
	<node name="foo_5" type="object">
		<node name="foo_5_a" type="string">value</node>
	</node>
	<node name="foo_6" type="array">
		<node name="foo_6_a" type="string">value</node>
	</node>
</registry>
```

with `Registry`

``` php
$registry = new Registry;

// Load file or string
$registry->loadFile($xmlFile, 'xml');
$registry->loadString($xmlString, 'xml');
```

Outputs

```
Array(
	foo_1 => bar
	foo_2 => 1
	foo_3 => 42
	foo_4 => 3.1415
	foo_5 => Array(
		foo_5_a => value
	)
	foo_6 => Array(
		foo_6_a => value
	)
)
```

The names of the XML import nodes can be customised using options. For example:

``` php
$registry = new Registry(array(
    'name' => 'data',
    'nodeName' => 'value'
));

$registry->loadString('<data><value name="foo" type="string">bar</value></data>, 'xml');
```

## Installation via Composer

Add `"joomla/registry": "~1.0"` to the require block in your composer.json and then run `composer install`.

```json
{
	"require": {
		"joomla/registry": "~1.0"
	}
}
```

Alternatively, you can simply run the following from the command line:

```sh
composer require joomla/registry "~1.0"
```
