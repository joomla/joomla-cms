# This script will reset the database and run the test suite.
# The first parameter is the path and name of the config file to use (these are in the servers directory)

mysql -h 127.0.0.1 --force -u username --password=password selsampledata < resetdb.sql  # selsampledata is the database name
echo $1
phpunit --bootstrap $1 tests/TestSuite.php
