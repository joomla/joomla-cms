This directory holds run-time configuration information for Subversion
clients.  The configuration files all share the same syntax, but you
should examine a particular file to learn what configuration
directives are valid for that file.

The syntax is standard INI format:

   - Empty lines, and lines starting with '#', are ignored.
     The first significant line in a file must be a section header.

   - A section starts with a section header, which must start in
     the first column:

       [section-name]

   - An option, which must always appear within a section, is a pair
     (name, value).  There are two valid forms for defining an
     option, both of which must start in the first column:

       name: value
       name = value

     Whitespace around the separator (:, =) is optional.

   - Section and option names are case-insensitive, but case is
     preserved.

   - An option's value may be broken into several lines.  The value
     continuation lines must start with at least one whitespace.
     Trailing whitespace in the previous line, the newline character
     and the leading whitespace in the continuation line is compressed
     into a single space character.

   - All leading and trailing whitespace around a value is trimmed,
     but the whitespace within a value is preserved, with the
     exception of whitespace around line continuations, as
     described above.

   - When a value is a boolean, any of the following strings are
     recognised as truth values (case does not matter):

       true      false
       yes       no
       on        off
       1         0

   - When a value is a list, it is comma-separated.  Again, the
     whitespace around each element of the list is trimmed.

   - Option values may be expanded within a value by enclosing the
     option name in parentheses, preceded by a percent sign and
     followed by an 's':

       %(name)s

     The expansion is performed recursively and on demand, during
     svn_option_get.  The name is first searched for in the same
     section, then in the special [DEFAULT] section. If the name
     is not found, the whole '%(name)s' placeholder is left
     unchanged.

     Any modifications to the configuration data invalidate all
     previously expanded values, so that the next svn_option_get
     will take the modifications into account.

The syntax of the configuration files is a subset of the one used by
Python's ConfigParser module; see

   http://www.python.org/doc/current/lib/module-ConfigParser.html

Configuration data in the Windows registry
==========================================

On Windows, configuration data may also be stored in the registry.  The
functions svn_config_read and svn_config_merge will read from the
registry when passed file names of the form:

   REGISTRY:<hive>/path/to/config-key

The REGISTRY: prefix must be in upper case. The <hive> part must be
one of:

   HKLM for HKEY_LOCAL_MACHINE
   HKCU for HKEY_CURRENT_USER

The values in config-key represent the options in the [DEFAULT] section.
The keys below config-key represent other sections, and their values
represent the options. Only values of type REG_SZ whose name doesn't
start with a '#' will be used; other values, as well as the keys'
default values, will be ignored.


File locations
==============

Typically, Subversion uses two config directories, one for site-wide
configuration,

  Unix:
    /etc/subversion/servers
    /etc/subversion/config
    /etc/subversion/hairstyles
  Windows:
    %ALLUSERSPROFILE%\Application Data\Subversion\servers
    %ALLUSERSPROFILE%\Application Data\Subversion\config
    %ALLUSERSPROFILE%\Application Data\Subversion\hairstyles
    REGISTRY:HKLM\Software\Tigris.org\Subversion\Servers
    REGISTRY:HKLM\Software\Tigris.org\Subversion\Config
    REGISTRY:HKLM\Software\Tigris.org\Subversion\Hairstyles

and one for per-user configuration:

  Unix:
    ~/.subversion/servers
    ~/.subversion/config
    ~/.subversion/hairstyles
  Windows:
    %APPDATA%\Subversion\servers
    %APPDATA%\Subversion\config
    %APPDATA%\Subversion\hairstyles
    REGISTRY:HKCU\Software\Tigris.org\Subversion\Servers
    REGISTRY:HKCU\Software\Tigris.org\Subversion\Config
    REGISTRY:HKCU\Software\Tigris.org\Subversion\Hairstyles

