JPlatform
=========

JPlatform is a final class that cannot be modified by the developer. It
has a number of public constant pertaining to the platform version and
some static utility methods.

Constants
---------

`JPlatform::PRODUCT` == 'Joomla Platform', `JPlatform::RELEASE` ==
'11.4' // The release number of the platform., `JPlatform::MAINTENANCE`
== '0' // The point maintenance version if applicable.,
`JPlatform::STATUS` == 'Status' // The development status.,
`JPlatform::BUILD` == '0' // The build number for the platform, if
applicable., `JPlatform::CODE_NAME` == 'Brian Kernighan' // A human
readable code name for this version, usually an honorarium.,
`JPlatform::RELEASE_DATE` == '03-Jan-2012' // The official release date
for this version., `JPlatform::RELEASE_TIME` == '00:00' // The official
release time for this version, if applicable.,
`JPlatform::RELEASE_TIME_ZONE` == 'GMT' // The reference timezone for
the official release date and time., `JPlatform::COPYRIGHT` ==
'Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights
reserved.', `JPlatform::LINK_TEXT` == 'A link to the Joomla Project'

Methods
-------

JPlatform has three utility methods, one for testing the version and two
for display.

JPlatform::isCompatible(\$version) - Tests if \$version is the installed
version of the platform.
JPlatform::getShortVersion() - A short textual representation of the
platform version.
JPlatform::getLongVersion() - A really verbose representation of the
platform version.
    // Tests the required version of the platform.
    if (!JPlatform::isCompatible('11.4'))
    {
        throw new LogicException(sprintf('Platform version %s not compatible.', JPlatform::getShortVersion());
    }
