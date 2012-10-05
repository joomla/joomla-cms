Introduction
============

This is the introduction to the Joomla Platform.

Folder Structure
================

The following outlines the purpose of the top-level folder structure of
the Joomla Platform as found in the Github repository.

/build contains information relevant for building code style reports
about the platform. Output from various automated processes may also end
up in this folder.

/docs contain developer manuals in DocBook format.

/libraries contains all the server-side PHP code used in the Joomla
Platform API.

/media contains any client-side resources used by the platform.

/tests contains all of the unit tests used for quality control.

Bootstrapping
=============

Bootstrapping the Joomla Platform is done by including
`/libraries/import.php` in your application. This file will initiliase a
number of constants if they are not already defined by the developer
prior to including the import.php file:

JPATH\_PLATFORM
- The path to the Joomla Platform (where
loader.php
or
platform.php
is located, usually in the same folder as
import.php
).
IS\_WIN
- A boolean value, true if the platform is Microsoft Windows based.
IS\_MAC
- A boolean value, true if the platform is Apple OSX based.
IS\_UNIX
- A bollean value, true is the platform is some flavor or Unix, Linux or
similar (but not Mac).
The bootstrap file will loader the JPlatform and JLoader classes and
also JFactory, JException (legacy), JObject, JRequest (legacy), JText
and JRoute. Depending on the version, it may also register classes in a
transitional state that do not conform to the auto-loader standards.

Minimalistic Approach
---------------------

If required, the bootstrap file (import.php) can be ignored and a custom
solution can be implemented (such as if the core JFactory class is to be
overriden).

    // Define the base path.
    define('JPATH_PLATFORM', '/path/to/platform/');

    // Load the platform version and loader classes.
    require_once JPATH_PLATFORM . '/platform.php';
    require_once JPATH_PLATFORM . '/loader.php';

    // Setup the autoloaders.
    JLoader::setup();

    // Do custom registration if required.

Legacy Platform
---------------

The Joomla Platform also supports a legacy tree for API. It includes
many classes that are only used by the Joomla CMS, or classes and
packages that have been upgraded and introduced backward compatibility
issues. Bootstrapping the legacy Joomla Platform is done by including
`/libraries/import.legacy.php`. This instructs the auto-loader to look
for classes in the legacy tree first, and then in the core tree.

Platform Version
================

Platform version information can be found by accessing the JPlatform
class.

Class Auto-loading
==================

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

JDatabase should be located in
`JPATH_PLATFORM/joomla/database/database.php`

JDatabaseQuery should be located in
`JPATH_PLATFORM/joomla/database/query.php`

JDatabaseQueryMysql should be located in
`JPATH_PLATFORM/joomla/database/query/mysql.php`

There is no limit to the depth to which the auto-loader will search,
providing it forms a valid path based on the camel case natural of the
class name. Note that while acronyms and names such as HTML, XML and
MySQL have a standard presention in text, such terms must observe camel
case rules programmatically ("HTML" becomes "Html", "XML" becomes "Xml"
and so on).

The JLoader class allows additional customisation including, but not
limited to, providing the ability to override core classes and cater for
classes that do not conform with the auto-loader naming and path
convention.
