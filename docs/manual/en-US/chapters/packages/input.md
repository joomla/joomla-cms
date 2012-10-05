The Input Package
=================

The Input package provides a number of classes that can be used as an
alternative to using the static calls of the JRequest class. The package
comprises of four classes, JInput and three sub-classes extended from
it: JInputCli, JInputCookie and JInputFiles. An input object is
generally owned by the application and explicitly added to an
application class as a public property, such as can be found in
JApplication, JApplicationCli and JApplicationDaemon.

All classes in this package are supported by the auto-loader so can be
invoked at any time.
