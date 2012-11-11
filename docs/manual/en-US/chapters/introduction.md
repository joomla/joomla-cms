# Joomla Platform Manual

## Introduction

This is the introduction to the Joomla Platform.

## Folder Structure

The following outlines the purpose of the top-level folder structure of
the Joomla Platform as found in the GitHub repository.

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

The Joomla Platform implements a class auto-loader removing the need for
the developer to include files by hand, or by using a fall to the
`jimport` function (this is now discourage in favor of using
JLoader::register). Only class names that begin with upper case "J" will
be considered for auto-loading. Following the "J" prefix, the class name
must be in camel case and each segment of the name will represent a
folder path below `JPLATFORM_PATH/joomla` where the last segment of the
name is the name of the class file. If there is only one part to the
class name, the auto-loader will look for the file in a folder of the
same name. Folder names must be in lower case.

JDatabase should be located in `JPATH_PLATFORM/joomla/database/database.php`

JDatabaseQuery should be located in `JPATH_PLATFORM/joomla/database/query.php`

JDatabaseQueryMysql should be located in `JPATH_PLATFORM/joomla/database/query/mysql.php`

There is no limit to the depth to which the auto-loader will search,
providing it forms a valid path based on the camel case natural of the
class name. Note that while acronyms and names such as HTML, XML and
MySQL have a standard presention in text, such terms should observe camel
case rules programmatically ("HTML" becomes "Html", "XML" becomes "Xml"
and so on).

The `JLoader` class allows additional customisation including, but not
limited to, providing the ability to override core classes and cater for
classes that do not conform with the auto-loader naming and path
convention.

### JLoader

`JLoader` is the mainstay of the Joomla Platform as it controls
auto-loading of classes. Wherever possible, class names and paths should
conform to the auto-loader convention in the form:

`JClassname` located in `JPATH\_PLATFORM/joomla/classname/classname.php`, or
`JPathtoClassname` located in `JPATH\_PLATFORM/joomla/pathto/classname.php`.
However, deviations, and even overrides can be handled by `JLoader`'s
register and discover methods.

#### Registering Classes

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

#### Registering a Class Prefix

Since 12.1, there is the ability to register where the auto-loader will
look based on a class prefix (previously only the "J" prefix was
supported, bound to the `/libraries/joomla` folder). This allows for
several scenarios:

* A developer can register the prefix of custom classes, and a root path to allow the auto-loader to find them.
* A developer can register an extra path for an existing prefix (for example, this allows the Joomla CMS to have custom libraries but still using the "J" prefix).
* A developer can register a force override for a prefix. This could be used to completely override the core classes with a custom replacement.

```php
// Tell the auto-loader to also look in the /libraries/cms folder for "J" prefixed classes.
JLoader::registerPrefix('J', JPATH_PLATFORM . '/cms');

// Tell the auto-loader to look for classes starting with "Foo" in a specific folder.
JLoader::registerPrefix('Foo', '/path/to/custom/packages');

// Tell the auto-loader to reset the "J" prefix and point it to a custom fork of the platform.
JLoader::registerPrefix('J', '/my/platform/fork', true);
```

#### Discovering Classes

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
