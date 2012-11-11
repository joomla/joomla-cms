## The Crypt Package

The Crypt password provides a set of classes that can be used for
encrypting and hashing data.

### Interfaces

#### JCryptPassword

JCryptPassword is an interface that requires a class to be implemented
with a create and a verify method.

The create method should take a plain text password and a type and
return a hashed password.

The verify method should accept a plain text password and a hashed
password and return a boolean indicating whether or not the password
matched the password in the hash.

The JCryptPassword interface defines the following constants for use
with implementations:

- `JCryptPassword::BLOWFISH`
- `JCryptPassword::JOOMLA`
- `JCryptPassword::MD5`

### Classes

#### JCryptPasswordSimple

##### Usage

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
