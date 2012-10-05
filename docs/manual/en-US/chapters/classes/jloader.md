JLoader
=======

JLoader is the mainstay of the Joomla Platform as it controls
auto-loading of classes. Wherever possible, class names and paths should
conform to the auto-loader convention in the form:

JClassname
located in
JPATH\_PLATFORM/joomla/classname/classname.php
, or
JPathtoClassname
located in
JPATH\_PLATFORM/joomla/pathto/classname.php
However, deviations, and even overrides can be handled by JLoader's
register and discover methods.

Registering Classes
-------------------

New classes, or override classes can be registered using the register
method. This method takes the class name, the path to the class file,
and an option boolean to force an update of the class register.

    // Register an adhoc class.
    JLoader::register('AdhocClass', '/the/path/adhoc.php');

    // Register a custom class to override as core class.
    // This must be done before the core class is loaded.
    JLoader::register('JDatabase', '/custom/path/database_driver.php', true);

Registering a Class Prefix
--------------------------

Since 12.1, there is the ability to register where the auto-loader will
look based on a class prefix (previously only the "J" prefix was
supported, bound to the `/libraries/joomla` folder). This allows for
several scenarios:

A developer can register the prefix of custom classes, and a root path
to allow the auto-loader to find them.
A developer can register an extra path for an existing prefix (for
example, this allows the Joomla CMS to have custom libraries but still
using the "J" prefix).
A developer can register a force override for a prefix. This could be
used to completely override the core classes with a custom replacement.
    // Tell the auto-loader to also look in the /libraries/cms folder for "J" prefixed classes.
    JLoader::registerPrefix('J', JPATH_PLATFORM . '/cms');

    // Tell the auto-loader to look for classes starting with "Foo" in a specific folder.
    JLoader::registerPrefix('Foo', '/path/to/custom/packages');

    // Tell the auto-loader to reset the "J" prefix and point it to a custom fork of the platform.
    JLoader::registerPrefix('J', '/my/platform/fork', true);

Discovering Classes
-------------------

Classes in a folder that follow a naming convention, but not one the
auto-loader immediately recognises, can be registered collectively with
JLoader's discover method. The discover method looks at the file names
in a folder and registers classes based on those names. Additional
arguments can be used to update the class register and recurse into
sub-folders.

    // Register all files in the /the/path/ folder as classes with a name like:
    // Prefix<Filename>
    JLoader::discover('Prefix', '/the/path/');
