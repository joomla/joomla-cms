#!/bin/bash
# Script for preparing the system tests in Joomla!

# Start apache
service apache2 restart

# Start Xvfb
export DISPLAY=:0
Xvfb -screen 0 1024x768x24 -ac +extension GLX +render -noreset &
sleep 1 # give xvfb some time to start

# Start Fluxbox
fluxbox &
sleep 3 # give fluxbox some time to start

# We include some xdg utilities next to the binary, and we want to prefer them
# over the system versions when we know the system versions are very old. We
# detect whether the system xdg utilities are sufficiently new to be likely to
# work for us by looking for xdg-settings. If we find it, we leave $PATH alone,
# so that the system xdg utilities (including any distro patches) will be used.
if ! which xdg-settings &> /dev/null; then
  # Old xdg utilities. Prepend $HERE to $PATH to use ours instead.
  export PATH="$HERE:$PATH"
else
  # Use system xdg utilities. But first create mimeapps.list if it doesn't
  # exist; some systems have bugs in xdg-mime that make it fail without it.
  xdg_app_dir="${XDG_DATA_HOME:-$HOME/.local/share/applications}"
  mkdir -p "$xdg_app_dir"
  [ -f "$xdg_app_dir/mimeapps.list" ] || touch "$xdg_app_dir/mimeapps.list"
fi

# Always use our versions of ffmpeg libs.
# This also makes RPMs find the compatibly-named library symlinks.
if [[ -n "$LD_LIBRARY_PATH" ]]; then
  LD_LIBRARY_PATH="$HERE:$HERE/lib:$LD_LIBRARY_PATH"
else
  LD_LIBRARY_PATH="$HERE:$HERE/lib"
fi
export LD_LIBRARY_PATH

export CHROME_VERSION_EXTRA="stable"

# We don't want bug-buddy intercepting our crashes. http://crbug.com/24120
export GNOME_DISABLE_CRASH_DIALOG=SET_BY_GOOGLE_CHROME

# Automagically migrate user data directory.
# TODO(phajdan.jr): Remove along with migration code in the browser for M33.
if [[ -n "" ]]; then
  if [[ ! -d "" ]]; then
    "$HERE/chrome" "--migrate-data-dir-for-sxs=" \
      --enable-logging=stderr --log-level=0
  fi
fi

# Make sure that the profile directory specified in the environment, if any,
# overrides the default.
if [[ -n "$CHROME_USER_DATA_DIR" ]]; then
  PROFILE_DIRECTORY_FLAG="--user-data-dir=$CHROME_USER_DATA_DIR"
fi

# Sanitize std{in,out,err} because they'll be shared with untrusted child
# processes (http://crbug.com/376567).
exec < /dev/null
exec > >(exec cat)
exec 2> >(exec cat >&2)

# Note: exec -a below is a bashism.
# DOCKER SELENIUM NOTE: Strait copy of script installed by Chrome with the exception of adding
# the --no-sandbox flag here.
exec -a "$0" "/usr/bin/google-chrome" --no-sandbox "$PROFILE_DIRECTORY_FLAG" \
  "$@"
exec -a "$0" /etc/alternatives/google-chrome --no-sandbox "$@"


# Move folder to /tests
ln -s $(pwd) /tests/www

# Composer install in tests folder
cd tests/codeception
composer install
cd ../..

# Run the tests
cp RoboFile.dist.ini RoboFile.ini
cp tests/codeception/acceptance.suite.dist.yml tests/codeception/acceptance.suite.yml

# Run tests
./tests/codeception/vendor/bin/robo run:tests
