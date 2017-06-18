# Start apache
sudo service apache2 restart

# Start Xvfb
sudo bash /etc/init.d/xvfb start
sleep 1 # give xvfb some time to start

# Start Fluxbox
fluxbox &
sleep 3 # give fluxbox some time to start

# Composer install in tests folder
cd tests/codeception
composer install
cd $BASE

# Run the tests
sudo cp RoboFile.dist.ini RoboFile.ini
sudo cp tests/codeception/acceptance.suite.dist.yml tests/codeception/acceptance.suite.yml