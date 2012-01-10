## Joomla! CMS on SQLite DB instructions

### Installation

Use the following settings in installation step 4 (Database)

* Database Type: Guess.. ```Sqlite``` ;)

* Host name: use the default ```localhost``` to create the database in ```JROOT/db``` otherwise specify the full path to the directory where the database file will be created.
* Username: Not used
* Password: Not used
* Database Name: ```<name>``` The name of the database file.
* Table Prefix: ```<prefix>``` The table prefix to use.

**Please note** that the "required" but "Not used" fields must still contain some information (@todo)

### Known issues

* Finder still contains some ```CHAR_LENGTH``` queries.
* Finder wants to clone JDatabase this requires serialization (solved)
* If your (3pd) extension relies on MySQL files it will definetely not run, unless you (or the developer) provide the corresponding SQLite files.

### Core "hacks" applied (so far)

* JTable: should use ```null``` not ```0``` in primary keys for inserting new records (required also by PostgreSQL) ([commit](https://github.com/elkuku/joomla-cms/commit/5602c7928bd04703ed2eb4a51e6d92860de0781b))
* JTableContent: content should fill a nullDate for publish_down ([commit](https://github.com/elkuku/joomla-cms/commit/5b191e17a3ab21392b7b0b6796c6d88b5cb986b7))
* com_menus: ```except``` is a reserved word in SQLite ([commit](https://github.com/elkuku/joomla-cms/commit/273ebc066931299266597177528a49dc51ef6e4d))
* chr(0) characters must be escaped (not real a core issue..) ([commit](https://github.com/elkuku/joomla-cms/commit/0ba217df8aabd558710a53ce9bafc4dfdc1b6f2e)) - should be more general - also facing problems with finder..
