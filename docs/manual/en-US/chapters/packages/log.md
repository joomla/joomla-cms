## The Log Package

### Introduction

The Joomla Platform includes a Log package that allows for configurable,
hook-driven logging to a variety of formats.

The classes included in the Log package are `JLog`, `JLogEntry`,
`JLogger` as well as the classes `JLoggerDatabase`,
`JLoggerEcho`, `JLoggerFormattedText`, `JLoggerMessageQueue`, `JLoggerSyslog`
and `JLoggerW3C` which support formatting and storage. Of all these
classes, you will generally only use `JLog` in your projects.

Logging is a two-step process.

First you must add the add loggers to listen for log messages. Any
number of loggers can be configured to listen for log messages based on
a priority and a category. For example, you can configure all log
messages to be logged to the database, but also set just errors to be
logged to a file. To do this, you use the `JLog::addLogger` method.

After at least one logger is configured, you can then add messages using
the `JLog::addLogEntry` method where you can specify a message, and
optionally a priority (integer), category (string) and date.

### Logging priority

Before we look at any logging examples, we need to understand what the
priority is. The priority is an integer mask and is set using one or
more predefined constants in the `JLog` class. These are:

* JLog::EMERGENCY
* JLog::ALERT
* JLog::CRITICAL
* JLog::ERROR
* JLog::WARNING
* JLog::NOTICE
* JLog::INFO
* JLog::DEBUG

A final constant, `JLog::ALL` is also available which corresponds to hex
FFFF (16 bits). The other constants reserve the first eight bits for
system use. This allows the developer the last eight bits, hex 100 to
8000, for custom use if desired. As the values are for masking, they can
be mixed using any of the bitwise operators for *and*, *or*, *not* and
*xor*.

By default, loggers are added to listen for `JLog::ALL` priorities and log
entries are added using the `JLog::INFO` mask.

### Logging to files *(formattedtext)*

A very typical example of logging is the ability to log to a file, and
this is the default handler for logging. To do this add the
logger and then you can add log messages.

```php
// Initialise a basic logger with no options (once only).
JLog::addLogger(array());

// Add a message.
JLog::add('Logged');
```

As no logger has been specified in the `JLog::addLogger` call, the
"formattedtext" logger will be used. This will log the message to a file
called "error.php" in the log folder specified by the "log_path"
configuration variable (in the Joomla CMS, the default is `/logs/`). It
will look something like this:

    #<?php die('Forbidden.'); ?>
    #Date: 2011-06-17 02:56:21 UTC
    #Software: Joomla Platform 11.1 Stable [ Ember ] 01-Jun-2011 06:00 GMT

    #Fields: datetime   priority    category    message
    2011-06-17T03:06:44+00:00   INFO    -   Logged

The file is tab-delimited and the default columns are the timestamp, the
text representation of the priority, the category and finally the
message. The category is empty (a dash) because we didn't supply it.

To log a different priority, you can use code like:

```php
JLog::add('Logged 3', JLog::WARNING, 'Test');
```

The log file will now look similar to the following:

    2011-06-17T03:06:44+00:00 INFO - Logged
    2011-06-17T03:52:08+00:00 WARNING - Logged 2
    2011-06-17T03:57:03+00:00 WARNING test Logged 3

#### Additional options with formattedtext

When adding the "formattedtext" logger, the following options are
available to supply in the array you pass to `JLog::addLogger`.

Option              | Description
------------------- | ----------------
text\_file          | Allows you to specify the name of the file to which messages are logged.
text\_file\_path    | Allows you to specify the folder path to the file to which messages are logged.
text\_file\_no\_php | If set, the PHP die statement will not be added to the file line of the file.
text\_entry\_format | Allows you to change the format of the entire line of the log message in the file.

### Changing the name of the log file

Given the options outlined in the previous section, you can change the
name of the file to which you are logging when you add the logger, like
this:

```php
// Log to a specific text file.
JLog::addLogger(
	array(
		'text_file' => 'mylogs.php'
	)
);
```

#### Logging different priorities to different files

You can log different types of messages to different files by adding
multiple loggers that bind different log priorities to different files.
For example, the following code will log all messages except errors to
one file, and error messages to a separate file.

```php
// Log all message except errors to mylogs.php.
JLog::addLogger(
	array(
		'text_file' => 'mylogs.php'
	),
	JLog::ALL ^ JLog::ERROR
);

// Log errors to myerrors.php.
JLog::addLogger(
	array(
		'text_file' => 'myerrors.php'
	),
	JLog::ERROR
);
```

#### Logging specific categories to a file

If you are wanting to collect errors for your specific project, class or
extension, you can also bind logging to different categories. For
example, the following code could be used in a Joomla extension to just
collect errors relating to it.

```php
// Log my extension errors only.
JLog::addLogger(
	array(
		'text_file' => 'com_hello.errors.php'
	),
	JLog::ERROR,
	'com_hello'
);
```

To log messages to that logger, you would use something similar to the
following code:

```php
JLog::add('Forgot to say goodbye', JLog::ERROR, 'com_hello');
```

It is important to note that other loggers, added beyond your control,
may also pick up this message.

#### Splitting up logs by date

Log files can, potentially, get very long over time. A convenient
solution to this is to roll logs into different files based on a period
of time - an hour, a day, a month or even a year. To do this, you just
need to add the date to the file name of the log file. The following
example shows you how to do this on a daily basis.

```php
// Get the date.
$date = JFactory::getDate()->format('Y-m-d');

// Add the logger.
JLog::addLogger(
	array(
		'text_file' => 'com_hello.'.$date.'.php'
	)
);
```

#### Changing the format of the log message

When you adding a log message, it is written to the file in a default
format in the form:

    {DATETIME} {PRIORITY} {CATEGORY} {MESSAGE}

Each field is written in upper case, wrapped in curly braces and
separated by tabs. There are a number of other fields that are
automatically defined in the "formattedtext" logger that you can take
advantage of automatically. These are:

Field      | Description
---------- | -----------
{CLIENTIP} | The IP address of the user.
{DATE}     | The "Y-m-d" date component of the message datestamp.
{TIME}     | The "H:i:s" time component of the message datestamp.

To modify for the log format to add any or all of these fields, you can
add the logger as shown in the following code.

```php
// Add the logger.
JLog::addLogger(
	array(
		'text_file' => 'com_hello.php',
		'text_entry_format' => '{DATE} {TIME} {CLIENTIP} {CATEGORY} {MESSAGE}' 
	)
);
```

As you can see, you can include or leave out any fields as you require
to suit the needs of your project.

You can also add more fields but to do this you need to create and add a
`JLogEntry` object directly. The following example shows you how to do
this.

```php
// Add the logger.
JLog::addLogger(
	array(
		'text_file' => 'com_shop.sales.php',
		'text_entry_format' => '{DATETIME} {PRICE} {QUANTITY} {MESSAGE}'
	),
	JLog::INFO,
	'Shop'
);

$logEntry = new JLogEntry('T- Shirt', JLog::INFO, 'Shop');
$logEntry->price = '7.99';
$logEntry->quantity = 10;

JLog::add($logEntry);
```

It is strongly recommended that, when using a custom format, you bind
the log entries to a specific and unique category, otherwise log files
with different format *(fields)* could become mixed.

### Logging to the database

The "database" logger allows you to log message to a database table. The
create syntax for the default table is as follows:

```sql
CREATE TABLE `jos_log_entries` (
	`priority` int(11) DEFAULT NULL,
	`message` varchar(512) DEFAULT NULL,
	`date` datetime DEFAULT NULL,
	`category` varchar(255) DEFAULT NULL,
	KEY `idx_category_date_priority` (`category`,`date`,`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

To log messages using the "database" logger, you the following code as a
guide.

```php
// Add the logger.
JLog::addLogger(
	array(
		'logger' => 'database'
	),
	JLog::ALL,
	'dblog'
);

// Add the message.
JLog::add('Database log', JLog::INFO, 'dblog');
```

Notice that the example binds the logger to all message priorities, but
only those with a category of "dblog".

If you are wanting to store additional information in the message, you
can do so using a JSON encoded string. For example:

```php
// Assemble the log message.
$user = JFactory::getUser();
$log = array(
	'userId' => $user->get('id'),
	'userName' => $user->get('name'),
	'stockId' => 'SKU123',
	'price' => '7.49',
	'quantity' => 10
);

// Add the message.
JLog::add(json_encode($log), JLog::INFO, 'dblog');
```

This makes it possible to retrieve detailed information for display.
