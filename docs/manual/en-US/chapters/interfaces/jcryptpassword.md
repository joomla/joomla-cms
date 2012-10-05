JCryptPassword
==============

JCryptPassword is an interface that requires a class to be implemented
with a create and a verify method.

The create method should take a plain text password and a type and
return a hashed password.

The verify method should accept a plain text password and a hashed
password and return a boolean indicating whether or not the password
matched the password in the hash.

The JCryptPassword interface defines the following constants for use
with implementations:

-   `JCryptPassword::BLOWFISH`

-   `JCryptPassword::JOOMLA`

-   `JCryptPassword::MD5`


