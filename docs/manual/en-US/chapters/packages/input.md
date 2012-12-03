## The Input Package

This package comprises of four classes, `JInput`and three sub-classes extended from it: `JInputCli`, `JInputCookie` and `JInputFiles`. It replaces the role of the now deprecated `JRequest` class. An input object is generally owned by the application and explicitly added to an application class as a public property, such as can be found in `JApplicationWeb`, `JApplicationCli` and `JApplicationDaemon`. 

The intent of this package is to abstract out the input source to allow code to be reused in different applications and in different contexts through dependency injection. For example, a controller could inspect the request variables directly using `JRequest`. But suppose there is a requirement to add a web service that carries input as a JSON payload. Instead of writing a second controller to handle the different input source, it would be much easier to inject an  input object, that is tailored for the type of input source, into the controller.

Using a `JInput` object through dependency injection also makes code easier to test. Writing unit tests for code that relies on `JRequest` is problematic to say the least.

All classes in this package are supported by the auto-loader so can be invoked at any time. 

### JInput

#### Construction

Unlike its predecessor `JRequest` which is used statically, the `JInput`
class is meant to be used as an instantiated concrete class. Among other
things, this makes testing of the class, and the classes that are
coupled to it, easier, but also means the developer has a lot more
flexibility since this allows for dependency injection.

The constructor takes two optional array arguments. The first is the
source data which defaults to the superglobal `$_REQUEST` if omitted or
`null`. The second is a general options array for which "filter" is the
only option key currently supported. If omitted, `JInput` will just use
the default instance of `JFilterInput`.

```php
// Default construction (data comes from $_REQUEST).
$input = new JInput;

// Construction with data injection.
$input = new JInput(array('foo' => 'bar');

// Construction with a custom filter.
$filter = JFilterInput::getInstance(/* custom settings */);
$input = new JInput(null, $filter);
```

#### Usage

The most common usage of the `JInput` class will be through the get method
which is roughly equivalent to the `JRequest::getVar` method. The get
method takes three arguments: a key name, a default value and a filter
name (defaulting to "cmd" if omitted). The filter name is any valid
filter type that the `JFilterInput` class, or the custom class provided in
the constructor, supports.

The set method is also equivalent to `JRequest::setVar` as is the
getMethod method.

```php
$input = new Jinput;

// Get the "foo" variable from the request.
$foo = $input->get('foo');

// If the variable is not available, use a default.
$foo = $input->get('foo', 'bar');

// Apply a custom filter to the variable, in this case, get the raw value.
$foo = $input->get('body', null, 'string');

// Explicitly set an input value.
$input->set('hidemainmenu', true);

// Get the request method used (assuming a web application example), returned in upper case.
if ($input->getMethod() == 'POST')
{
	// Do something.
}
```

The filter types available when using JFilterInput are:

* INT, INTEGER - Matches the first, signed integer value.
* UINT - Matches the first unsigned integer value.
* FLOAT, DOUBLE - Matches the first floating point number.
* BOOL, BOOLEAN - Converts the value to a boolean data type.
* WORD - Allows only case insensitive A-Z and underscores.
* ALNUM - Allows only case insensitive A-Z and digits.
* CMD - Allows only case insensitive A-Z, underscores, periods and dashes.
* BASE64 - Allows only case insensitive A-Z, forward slash, plus and equals.
* STRING - Returns a fully decoded string.
* HTML - Returns a string with HTML entities and tags intact, subject to the white or black lists in the filter.
* ARRAY - Returns the source as an array with no additional filtering applied.
* PATH - Matches legal characters for a path.
* USERNAME - Strips a select set of characters from the source (\\x00, -, \\x1F, \\x7F, \<, \>, ", ', %, &).

If no filter type is specified, the default handling of `JFilterInput` is
to return an aggressively cleaned and trimmed string, stripped of any
HTML or encoded characters.

Additionally, magic getters are available as shortcuts to specific
filter types.

```php
$input = new JInput;

// Apply the "INT" filter type.
$id = $input->getInt('id');

// Apply the "WORD" filter type.
$folder = $input->getWord('folder', 'images');

// Apply the "USERNAME" filter.
$ntLogin = $input->getUsername('login');

// Using an unknown filter. It works, but is treated the same as getString.
$foo = $input->getFoo('foo');
```

The class also supports a magic get method that allows you shortcut
access to other superglobals such as `$_POST`, etc, but returning them
as a `JInput` object.

```php
$input = new JInput;

// Get the $_POST superglobal.
$post = $input->post;

// Access a server setting as if it's a JInput object.
if ($input->server->get('SERVER_ADDR'))
{
	// Do something with the IP address.
}

// Access an ENV variable.
$host = $input->env->get('HOSTNAME');
```

#### Serialization

The `JInput` class implements the `Serializable` interface so that it can be
safely serialized and unserialized. Note that when serializing the "ENV"
and "SERVER" inputs are removed from the class as they may conflict or
inappropriately overwrite settings during unserialization. This allows
for `JInput` objects to be safely used with cached data.

### JInputCli

The JInputCli class is extended from `JInput` but is tailored to work with
command line input. Once again the get method is used to get values of
command line variables in short name format (one or more individual
characters following a single dash) or long format (a variable name
followed by two dashes). Additional arguments can be found be accessing
the args property of the input object.

An instance of `JInputCli` will rarely be instantiated directly. Instead,
it would be used implicitly as a part of an application built from
`JAppcliationCli` as shown in the following example.

```php
#!/usr/bin/php
<?php
/**
 * This file is saved as argv.php
 *
 * @package  Examples
 */

/**
 * An example command line application.
 *
 * @package  Examples
 * @since    1.0
 */
class Argv extends JApplicationCli
{
	/**
	 * Execute the application.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function execute()
	{
		var_dump($this->input->get('a'));
		var_dump($this->input->get('set'));
		var_dump($this->input->args);
	}
}
```

```
> ./argv.php
bool(false)
bool(false)
array(0) {}

> ./argv.php -a --set=match
bool(true)
string(5) "match"
array(0) {}

> ./argv.php -a value
string(5) "value"
bool(false)
array(0) {}

> ./argv.php -a foo bar
string(3) "foo"
bool(false)
array(1) {[0] => string(3) "bar"}
```

### JInputCookie

> Can you help improve this section of the manual?

### JInputFiles

The `JInputFiles` class provides a way to handle file attachments as payloads of POSTed forms. Consider the following form which is assumed to handle an array of files to be attached (through some JavaScript behavior):

```html
<form method="POST" action="/files" enctype="multipart/form-data">
   Attachments:
   <input type="file" name="attachments[]" />
   <button>Add another file</button>
</form>
```

Access the files from the request could be done as follows:

```php
// By default, a new JInputFiles will inspect $_FILES.
$input = new JInputFiles;
$files = $input->get('attachments');

echo 'Inspecting $_FILES:';
var_dump($_FILES);

echo 'Inspecting $files:';
var_dump($files);
```

```
Inspecting $_FILES:

array
  'name' => 
    array
      0 => string 'aje_sig_small.png' (length=17)
      1 => string '' (length=0)
  'type' => 
    array
      0 => string 'image/png' (length=9)
      1 => string '' (length=0)
  'tmp_name' => 
    array
      0 => string '/private/var/tmp/phpPfGfnN' (length=26)
      1 => string '' (length=0)
  'error' => 
    array
      0 => int 0
      1 => int 4
  'size' => 
    array
      0 => int 16225
      1 => int 0

Inspectiong $files:

array
  0 => 
    array
      'name' => string 'sig_small.png' (length=17)
      'type' => string 'image/png' (length=9)
      'tmp_name' => string '/private/var/tmp/phpybKghO' (length=26)
      'error' => int 0
      'size' => int 16225
  1 => 
    array
      'name' => string '' (length=0)
      'type' => string '' (length=0)
      'tmp_name' => string '' (length=0)
      'error' => int 4
      'size' => int 0
```

Unlike the PHP `$_FILES` supergobal, this array is very easier to parse. The example above assumes two files were submitted, but only one was specified. The 'blank' file contains an error code (see [PHP file upload errors](http://php.net/manual/en/features.file-upload.errors.php)).

The `set` method is disabled in `JInputFiles`. However, 