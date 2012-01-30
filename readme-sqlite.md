## Joomla! CMS on SQLite DB instructions

### Installation

Use the following settings in installation step 4 (Database)

* **Database Type** Guess.. Choose ```Sqlite``` or stop reading right here ```;)```
* **Host name** This sets the **path** where your database **file** is being stored. You have three (3) options here:
<ol>
<li>```blank``` (default) Leave the field blank to set the database direcory to the name ```data``` one directory **above** your Joomla! root directory. That is ```JROOT/../data```</li>
<li>```localhost``` Use this option to create the database **inside** your Joomla! root directory in a folder called **db**. That is ```JROOT/db```.</li>
<li>```/path/to/directory``` Specify the full path to the directory where the database file should be created. The directory must already exist.</li>
</ol>
* **Database Name** ```<name>``` The name of the database **file** (e.g. ```mydatabase.sdb```).
* **Table Prefix** ```<prefix>``` The table prefix to use (e.g. ```abc123_```). The prefix must end with an underscore.

You might also choose to install the **sample data** in the last step.
Note that this will take more time than on MySQL due to a "special" sample data SQL file - please be patient ```;)```

The Joomla! CMS should be installed and running now. Please report any arrors - Thx.

### Known issues

* <del>Finder still contains some ```CHAR_LENGTH``` queries</del> - solved by the core team.
* <del>Finder wants to clone JDatabase this requires serialization</del> - solved (somewhat)
* If your (3pd) extension relies on MySQL files it will definetely not run, unless you (or the developer) provide the corresponding SQLite files.

	**Update**: You may also use the technique described below to use a single XML install file for multiple database engines.

### Additional features

#### CMS Installer tweaked for multiple database engines

* A new set of classes in ```JROOT/libraries/cms/database/installer``` allow specific options for multiple database engines during the CMS installation process.

#### Extension installer tweaked for multiple database engines

* A new set of classes in ```JROOT/libraries/cms/database/importer``` allow the conversion of install information given in a XML file to the appropiate SQL syntax for the current database engin in use. Currently supported are: MySQL, PostgreSQL and SQLite.

	This is targeted at 3rd party extension developers.

	The already available XML exporter for MySQL has been fixed to allow the export of MySQL databases to XML format.

This will allow extension developers to ship their extensions with a single install file for multiple database engines.

#### Maintainance script

* A new set of classes in ```JROOT/libraries/cms/database/maintainer``` allow driver specific maintainance tasks like optimize and backup the database. This classes can be used by 3pd extensions or by the:

	```JROOT/cli/dbmaintainer.php``` CLI script for database maintainance tasks. This script can execute tasks like optimize and backup the database and must be called from the command line. It can be used by cron tasks.

### Core "hacks" applied (so far)

Thoses "hacks" (fixes) have been applied to allow the execution of the Joomla! CMS core extensions on Postgres and SQLite databases.

* JTable: should use ```null``` not ```0``` in primary keys for inserting new records (required also by PostgreSQL) ([commit](https://github.com/elkuku/joomla-cms/commit/5602c7928bd04703ed2eb4a51e6d92860de0781b))
* JTableContent: content should fill a nullDate for publish_down ([commit](https://github.com/elkuku/joomla-cms/commit/5b191e17a3ab21392b7b0b6796c6d88b5cb986b7))
* com_menus: ```except``` is a reserved word in SQLite ([commit](https://github.com/elkuku/joomla-cms/commit/273ebc066931299266597177528a49dc51ef6e4d))
* chr(0) characters must be escaped (not real a core issue..) ([commit](https://github.com/elkuku/joomla-cms/commit/0ba217df8aabd558710a53ce9bafc4dfdc1b6f2e)) - should be more general - also facing problems with finder..

### More Information on SQLite

* http://www.sqlite.org/
* http://en.wikipedia.org/wiki/SQLite

PHP

* http://php.net/manual/en/ref.pdo-sqlite.php
* http://www.phpro.org/tutorials/Introduction-to-PHP-PDO.html

Management Tools

* List of SQLite management tools: http://www.sqlite.org/cvstrac/wiki?p=ManagementTools
* My favourite ;) http://sqliteman.com/
* A FireFox Addon: https://addons.mozilla.org/de/firefox/addon/sqlite-manager/

### BTW
Mediawiki also runs on SQLite http://www.mediawiki.org/wiki/Manual:SQLite
