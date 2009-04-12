NAME:

    Securimage - A PHP class for creating and managing form CAPTCHA images

VERSION: 1.0.2

AUTHOR:

    Drew Phillips <drew@drew-phillips.com>

DOWNLOAD:

    The latest version can always be
    found at http://www.phpcaptcha.org

DOCUMENTATION:

    Online documentation of the class, methods, and variables can
    be found at http://www.phpcaptcha.org/Securimage_Docs/

REQUIREMENTS:
    PHP 4.3.0
    GD  2.0
    FreeType (optional, required for TTF support)

SYNOPSIS:

    require_once 'securimage.php';

    $image = new Securimage();

    $image->show();

    // Code Validation

    $image = new Securimage();
    if ($image->check($_POST['code']) == true) {
      echo "Correct!";
    } else {
      echo "Sorry, wrong code.";
    }

DESCRIPTION:

    What is Securimage?

    Securimage is a PHP class that is used to generate and validate CAPTCHA images.
    The classes uses an existing PHP session or creates its own if none is found to store the
    CAPTCHA code.  Variables within the class are used to control the style and display of the image.
    The class supports TTF fonts and effects for strengthening the security of the image.
    If TTF support is not available, GD fonts can be used as well, but certain options such as
    transparent text and angled letters cannot be used.


COPYRIGHT:
    Copyright (c) 2007 Drew Phillips. All rights reserved.
    This software is released under the GNU Lesser General Public License.
