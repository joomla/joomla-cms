<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

/** @var string $minphp */
?>

================================================================================
WARNING! Incompatible PHP version <?php echo PHP_VERSION ?> (required: <?php echo $minphp ?> or later)
================================================================================

This script must be run using PHP version <?php echo $minphp ?> or later. Your server is
currently using a much older version which would cause this script to crash. As
a result we have aborted execution of the script. Please contact your host and
ask them for the correct path to the PHP CLI binary for PHP <?php echo $minphp ?> or later, then
edit your CRON job and replace your current path to PHP with the one your host
gave you.

For your information, the current PHP version information is as follows.

PATH:    <?php echo PHP_BINDIR ?>
VERSION: <?php echo PHP_VERSION ?>

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
IMPORTANT!
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
PHP version numbers are NOT decimals! Trailing zeros do matter. For example,
PHP 5.3.28 is twenty four versions newer (greater than) than PHP 5.3.4.
Please consult https://www.akeeba.com/how-do-version-numbers-work.html


Further clarifications:

1. There is no possible way that you are receiving this message in error. We
are using the PHP_VERSION constant to detect the PHP version you are
currently using. This is what PHP itself reports as its own version. It
simply cannot lie.

2. Even though your *site* may be running in a higher PHP version that the one
reported above, your CRON scripts will most likely not be running under it.
This has to do with the fact that your site DOES NOT run under the command
line and there are different executable files (binaries) for the web and
command line versions of PHP.

3. Please note that we cannot provide support about this error as the solution
depends only on your server setup. The only people who know how your server
is set up are your host's technicians. Therefore we can only advise you to
contact your host and request them the correct path to the PHP CLI binary.
Let us stress out that only your host knows and can give this information
to you.

4. The latest published versions of PHP can be found at http://www.php.net/
Any older version is considered insecure and must not be used on a
production site. If your server uses a much older version of PHP than those
published in the URL above please notify your host that their servers are
insecure and in need of an update.

This script will now terminate. Goodbye.

