## SQLite instructions

### Installation

Use the following settings in installation step 4 (Database)

* Host name: Not used
* Username: Not used
* Password: Not used
* Database Name: ```<name>``` A file with the specified ```<name>``` will be created at ```JROOT/db/<name>```
* Table Prefix: ```<prefix>``` The table prefix

Please note that the "required" but "Not used" fields must still contain some information (@todo)

**Note** Currently the install.sql includes also the sample data (for testing).
If you want to install without sample data, please rename the file ```installation/sql/sqlite/joomla-clean.sql``` to ```joomla.sql```, and the original ```joomla.sql``` to somethig else.
