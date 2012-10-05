JInput
======

Construction
------------

Unlike its predecessor JRequest which is used statically, the JInput
class is meant to be used as an instantiated concrete class. Among other
things, this makes testing of the class, and the classes that are
coupled to it, easier, but also means the developer has a lot more
flexibility since this allows for dependency injection.

The constructor takes two optional array arguments. The first is the
source data which defaults to the superglobal \$\_REQUEST if omitted or
null. The second is a general options array for which "filter" is the
only option key currently supported. If omitted, JInput will just use
the default instance of JFilterInput.

    // Default construction (data comes from $_REQUEST).
    $input = new JInput;

    // Construction with data injection.
    $input = new JInput(array('foo' => 'bar');

    // Construction with a custom filter.
    $filter = JFilterInput::getInstance(/* custom settings */);
    $input = new JInput(null, $filter);

Usage
-----

The most common usage of the JInput class will be through the get method
which is roughly equivalent to the JRequest::getVar method. The get
method takes three arguments: a key name, a default value and a filter
name (defaulting to "cmd" if omitted). The filter name is any valid
filter type that the JFilterInput class, or the custom class provided in
the constructor, supports.

The set method is also equivalent to JRequest::setVar as is the
getMethod method.

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

The filter types available when using JFilterInput are:

INT, INTEGER - Matches the first, signed integer value.
UINT - Matches the first unsigned integer value.
FLOAT, DOUBLE - Matches the first floating point number.
BOOL, BOOLEAN - Converts the value to a boolean data type.
WORD - Allows only case insensitive A-Z and underscores.
ALNUM - Allows only case insensitive A-Z and digits.
CMD - Allows only case insensitive A-Z, underscores, periods and dashes.
BASE64 - Allows only case insensitive A-Z, forward slash, plus and
equals.
STRING - Returns a fully decoded string.
HTML - Returns a string with HTML entities and tags intact, subject to
the white or black lists in the filter.
ARRAY - Returns the source as an array with no additional filtering
applied.
PATH - Matches legal characters for a path.
USERNAME - Strips a select set of characters from the source (\\x00, -,
\\x1F, \\x7F, \<, \>, ", ', %, &).
If no filter type is specified, the default handling of JFilterInput is
to return an aggressively cleaned and trimmed string, stripped of any
HTML or encoded characters.

Additionally, magic getters are available as shortcuts to specific
filter types.

    $input = new JInput;

    // Apply the "INT" filter type.
    $id = $input->getInt('id');

    // Apply the "WORD" filter type.
    $folder = $input->getWord('folder', 'images');

    // Apply the "USERNAME" filter.
    $ntLogin = $input->getUsername('login');

    // Using an unknown filter. It works, but is treated the same as getString.
    $foo = $input->getFoo('foo');

The class also supports a magic get method that allows you shortcut
access to other superglobals such as \$\_POST, etc, but returning them
as a JInput object.

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

Serialization
-------------

The JInput class implements the Serializable interface so that it can be
safely serialized and unserialized. Note that when serializing the "ENV"
and "SERVER" inputs are removed from the class as they may conflict or
inappropriately overwrite settings during unserialization. This allows
for JInput objects to be safely used with cached data.
