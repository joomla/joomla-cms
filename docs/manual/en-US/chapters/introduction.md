# Joomla Platform Manual

## Introduction

This is the introduction to the Joomla Platform.

## Folder Structure

The following outlines the purpose of the top-level folder structure of
the Joomla Platform as found in the [GitHub repository](https://github.com/joomla/joomla-platform/ "Joomla Platform Github repository").

Folder     | Description
---------- | --------------------
/build     | Contains information relevant for building code style reports about the platform. Output from various automated processes may also end up in this folder.
/docs      | Contain developer manuals in Markdown format.
/libraries | Contains all the server-side PHP code used in the Joomla Platform API.
/media     | Contains any client-side resources used by the platform.
/tests     | Contains all of the unit tests used for quality control.

## Bootstrapping

Bootstrapping the Joomla Platform is done by including
`/libraries/import.php` in your application. This file will initiliase a
number of constants if they are not already defined by the developer
prior to including the import.php file:

Name             | Description
---------------- | -----------------------
JPATH\_PLATFORM  | The path to the Joomla Platform (where `loader.php` or `platform.php` is located, usually in the same folder as `import.php`).
IS\_WIN          | A boolean value, true if the platform is Microsoft Windows based.
IS\_MAC          | A boolean value, true if the platform is Apple OSX based.
IS\_UNIX         | A bollean value, true is the platform is some flavor or Unix, Linux or similar (but not Mac).

The bootstrap file will load the `JPlatform` and `JLoader` classes and
also `JFactory`, `JException` (legacy), `JObject`, `JRequest` (legacy), `JText`
and `JRoute`. Depending on the version, it may also register classes in a
transitional state that do not conform to the auto-loader standards.

### Minimalistic Approach

If required, the bootstrap file (`import.php`) can be ignored and a custom
solution can be implemented (such as if the core `JFactory` class is to be
overriden).

```php
// Define the base path.
define('JPATH_PLATFORM', '/path/to/platform/');

// Load the platform version and loader classes.
require_once JPATH_PLATFORM . '/platform.php';
require_once JPATH_PLATFORM . '/loader.php';

// Setup the autoloaders.
JLoader::setup();

// Do custom registration if required.
```

### Legacy Platform

The Joomla Platform also supports a legacy tree for API. It includes
many classes that are only used by the Joomla CMS, or classes and
packages that have been upgraded and introduced backward compatibility
issues. Bootstrapping the legacy Joomla Platform is done by including
`/libraries/import.legacy.php`. This instructs the auto-loader to look
for classes in the legacy tree first, and then in the core tree.

### Using Phar

The Joomla Platform can be packed into a Phar file (a PHP archive). This can allow a developer to ship an application with the Platform in a compressed format so that everything can 'just work' without downloading the Platform separately. In large bespoke projects it also provides a convenient way to update the Joomla Platform with a single file and also ensuring that development and production environments are all set up with the same version of the Platform.

To make a Phar of the Platform first download the Packager Tool from [https://github.com/LouisLandry/packager/downloads](https://github.com/LouisLandry/packager/downloads) and put the `joomla-packager.phar` file in your operating system's executable path. This is actually a standalone application with selected parts of the Joomla Platform included with it (it's one of those applications that uses itself to build itself).

To create the phar of the core Joomla Platform (without the legacy tree) go to the root folder of the Joomla Platform and execute `joomla-packager.phar`. This will build automatically detect the `packager.xml` configuration file and place the phar file in `build/joomla.phar`. You can then include this phar file in your application by using the following code:

```php
require_once 'path/to/joomla.phar';
```

After this you can just start using the Platform API as required.

For advanced applications or projects that include their own unit test suite, the Platform and test framework can also be built into a phar by executing the following:

```bash
$ joomla-packager.phar -f packager.test.phar
```

## Platform Version

Platform version information can be found by accessing the `JPlatform`
class.

### JPlatform

`JPlatform` is a final class that cannot be modified by the developer. It
has a number of public constant pertaining to the platform version and
some static utility methods.

#### Constants

Name                           | Description
------------------------------ | ---------------------------
JPlatform::PRODUCT             | Joomla Platform
JPlatform::RELEASE             | The release number of the platform.
JPlatform::MAINTENANCE         | The point maintenance version if applicable.
JPlatform::STATUS              | The development status.
JPlatform::BUILD               | The build number for the platform, if applicable.
JPlatform::CODE\_NAME          | A human readable code name for this version.
JPlatform::RELEASE\_DATE       | The official release date for this version.
JPlatform::RELEASE\_TIME       | The official release time for this version, if applicable.
JPlatform::RELEASE\_TIME\_ZONE | The timezone for the official release date and time.
JPlatform::COPYRIGHT           | The copyright statement.
JPlatform::LINK\_TEXT          | An HTML hyperlink to the Joomla Project.

#### Methods

`JPlatform` has three utility methods, one for testing the version and two
for display.

Method                            | Description
--------------------------------- | ------------------------
JPlatform::isCompatible($version) | Tests if $version is the installed version of the platform.
JPlatform::getShortVersion()      | A short textual representation of the platform version.
JPlatform::getLongVersion()       | A really verbose representation of the platform version.

```php
    // Tests the required version of the platform.
    if (!JPlatform::isCompatible('11.4'))
    {
        throw new LogicException(sprintf('Platform version %s not compatible.', JPlatform::getShortVersion());
    }
```

## Class Auto-loading

`JLoader` is the mainstay of the Joomla Platform as it controls auto-loading of classes.

It removes the need for the developer to include files by hand, or by using a fall to the `jimport` function.

Multiple ways of auto loading classes, following different conventions are proposed by JLoader.

### The Namespace Loader

Since the release 12.3 of the Joomla Platform there is the possibility to auto classes within namespaces.

* A developer can register the full path to a top level (root) namespace where the loader can find classes (within this namespace).

* A developer can override an existing namespace path by replacing it with a new one.

* A developer can register multiple paths to the same namespace.

#### Convention

The convention is to have the namespace names matching the directories names.

For example :

```php
<?php
namespace Chess\Piece;

class Pawn
{

}
```

must be found in `BASE_PATH/chess/piece/pawn.php` or in `BASE_PATH/Chess/Piece/Pawn.php`.

For the namespace declaration, it is recommanded to use camel case letters as you will have for a class name.

But as you saw above there are different possibilities for the paths case :

#### Lower Case :

The directory structure is lower case and the namespace can be any case.

It must be used when the path is lower case and the namespace camel case.

Example :

```php
<?php
namespace Chess\Piece;

class Pawn
{

}
```

for a class in `BASE_PATH/chess/piece/pawn.php`.

#### Natural Case :

The namespace case matches the path case.

It must be used when you have lower case namespaces and paths or when you have camel case namespaces and paths.

Examples :

```php
<?php
namespace Chess\Piece;

class Pawn
{

}
```

for a class in `BASE_PATH/Chess/Pieces/Pawn.php`.

```php
<?php
namespace chess\piece;

class Pawn
{

}
```

for a class in `BASE_PATH/chess/pieces/pawn.php`.

#### Mixed Case :

It regroups the two options.

It must be used when you have some lower case and camel case paths and camel case or lower case namespace declarations.

For example, Joomla can stay lower case and your application can have a camel case directory structure.
Both can be auto loaded using the same Mixed Case loader.

#### Usage

#### Setup the Loader

In order to correctly use the namespace auto loader you need to setup it according the case strategy you choosed.

```php
<?php

// Setup the loader with the Lower Case strategy.
JLoader::setup(JLoader::LOWER_CASE, true);

// Setup the loader with the Natural Case strategy.
JLoader::setup(JLoader::NATURAL_CASE, true);

// Setup the loader with the Mixed Case strategy.
JLoader::setup(JLoader::MIXED_CASE, true);
```

#### Registering a namespace

You can register a top level namespace by using `JLoader::registerNamespace`.

For example :

```php
<?php

// The two parameters are case sensitive.
// The first one must match the namespace declaration case.
// The second one must match the path case.
JLoader::registerNamespace('Chess', BASE_PATH . '/chess');
```

All classes respecting the naming and path convention will be auto loaded.

#### Appending an other path

```php
<?php

// Adding an other path to the Chess namespace.
JLoader::registerNamespace('Chess', AN_OTHER_PATH . '/chess');
```

#### Reseting a path

```php
<?php

// Reseting a path by adding an other one.
JLoader::registerNamespace('Chess', AN_OTHER_PATH . '/chess', true);
```

### The Prefix Loader

Since 12.1, there is the ability to register where the auto-loader will
look based on a class prefix (previously only the "J" prefix was
supported, bound to the `/libraries/joomla` folder). This allows for
several scenarios:

* A developer can register the prefix of custom classes, and a root path to allow the auto-loader to find them.
* A developer can register an extra path for an existing prefix (for example, this allows the Joomla CMS to have custom libraries but still using the "J" prefix).
* A developer can register a force override for a prefix. This could be used to completely override the core classes with a custom replacement.

#### Convention

The class name must be in camel case and each segment of the name will represent a folder path
where the last segment of the name is the name of the class file.
If there is only one part to the class name, the auto-loader will look for the file in a folder of the
same name.
Folder names must be in lower case.

Examples :

`PrefixUserModel` should be located in `PATH_TO_PREFIX/user/model.php`.

`PrefixUser` should be located in `PATH_TO_PREFIX/user/user.php`.

There is no limit to the depth to which the auto-loader will search,
providing it forms a valid path based on the camel case natural of the
class name.
Note that while acronyms and names such as HTML, XML and MySQL have a standard presention in text,
such terms should observe camel case rules programmatically ("HTML" becomes "Html", "XML" becomes "Xml"
and so on).

#### Usage

```php
// Tell the auto-loader to also look in the /libraries/cms folder for "J" prefixed classes.
JLoader::registerPrefix('J', JPATH_PLATFORM . '/cms');

// Tell the auto-loader to look for classes starting with "Foo" in a specific folder.
JLoader::registerPrefix('Foo', '/path/to/custom/packages');

// Tell the auto-loader to reset the "J" prefix and point it to a custom fork of the platform.
JLoader::registerPrefix('J', '/my/platform/fork', true);
```

### Registering Classes

New classes, or override classes can be registered using the register
method. This method takes the class name, the path to the class file,
and an option boolean to force an update of the class register.

```php
// Register an adhoc class.
JLoader::register('AdhocClass', '/the/path/adhoc.php');

// Register a custom class to override as core class.
// This must be done before the core class is loaded.
JLoader::register('JDatabase', '/custom/path/database_driver.php', true);
```

### Discovering Classes

Classes in a folder that follow a naming convention, but not one the
auto-loader immediately recognises, can be registered collectively with
`JLoader`'s discover method. The `discover` method looks at the file names
in a folder and registers classes based on those names. Additional
arguments can be used to update the class register and recurse into
sub-folders.

```php
// Register all files in the /the/path/ folder as classes with a name like:  Prefix<Filename>
JLoader::discover('Prefix', '/the/path/');
```
