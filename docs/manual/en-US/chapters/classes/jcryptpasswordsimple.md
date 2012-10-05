JCryptPasswordSimple
====================

Usage
-----

In addition to the interface JCryptPassword there is also a basic
implementation provided which provides for use with the most common
password schemes. This if found in the JCryptPasswordSimple class.

Aside from the two methods create and verify methods, this
implementation also adds an additional method called setCost. This
method is used to set a cost parameter for methods that support workload
factors. It takes an integer cost factor as a parameter.

JCryptPasswordSimple provides support for bcrypt, MD5 and the
traditional Joomla! CMS hashing scheme. The hash format can be specified
during hash creation by using the constants `JCryptPassword::BLOWFISH`,
`JCryptPassword::MD5` and `JCryptPassword::JOOMLA`. An appropriate salt
will be automatically generated when required.
