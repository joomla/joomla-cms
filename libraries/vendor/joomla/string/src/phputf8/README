++PHP UTF-8++

Version 0.5

++DOCUMENTATION++

Documentation in progress in ./docs dir

http://www.phpwact.org/php/i18n/charsets
http://www.phpwact.org/php/i18n/utf-8

Important Note: DO NOT use these functions without understanding WHY
you are using them. In particular, do not blindly replace all use of PHP's
string functions which functions found here - most of the time you will
not need to, and you will be introducing a significant performance
overhead to your application. You can get a good idea of when to use what
from reading: http://www.phpwact.org/php/i18n/utf-8

Important Note: For sake of performance most of the functions here are
not "defensive" (e.g. there is not extensive parameter checking, well
formed UTF-8 is assumed). This is particularily relevant when is comes to
catching badly formed UTF-8 - you should screen input on the "outer
perimeter" with help from functions in the utf8_validation.php and
utf8_bad.php files.

Important Note: this library treats ALL ASCII characters as valid, including ASCII control characters. But if you use some ASCII control characters in XML, it will render the XML ill-formed. Don't be a bozo: http://hsivonen.iki.fi/producing-xml/#controlchar

++BUGS / SUPPORT / FEATURE REQUESTS ++

Please report bugs to:
http://sourceforge.net/tracker/?group_id=142846&atid=753842
- if you are able, please submit a failing unit test
(http://www.lastcraft.com/simple_test.php) with your bug report.

For feature requests / faster implementation of functions found here,
please drop them in via the RFE tracker: http://sourceforge.net/tracker/?group_id=142846&atid=753845
Particularily interested in faster implementations!

For general support / help, use:
http://sourceforge.net/tracker/?group_id=142846&atid=753843

In the VERY WORST case, you can email me: hfuecks gmail com - I tend to be slow to respond though so be warned.

Important Note: when reporting bugs, please provide the following
information;

PHP version, whether the iconv extension is loaded (in PHP5 it's
there by default), whether the mbstring extension is loaded. The
following PHP script can be used to determine this information;

<?php
print "PHP Version: " .phpversion()."<br>";
if ( extension_loaded('mbstring') ) {
    print "mbstring available<br>";
} else {
    print "mbstring not available<br>";
}
if ( extension_loaded('iconv') ) {
    print "iconv available<br>";
} else {
    print "iconv not available<br>";
}
?>

++LICENSING++

Parts of the code in this library come from other places, under different
licenses.
The authors involved have been contacted (see below). Attribution for
which code came from elsewhere can be found in the source code itself.

+Andreas Gohr / Chris Smith - Dokuwiki
There is a fair degree of collaboration / exchange of ideas and code
beteen Dokuwiki's UTF-8 library;
http://dev.splitbrain.org/view/darcs/dokuwiki/inc/utf8.php
and phputf8. Although Dokuwiki is released under GPL, its UTF-8
library is released under LGPL, hence no conflict with phputf8

+Henri Sivonen (http://hsivonen.iki.fi/php-utf8/ /
http://hsivonen.iki.fi/php-utf8/) has also given permission for his
code to be released under the terms of the LGPL. He ported a Unicode / UTF-8
converter from the Mozilla codebase to PHP, which is re-used in phputf8
