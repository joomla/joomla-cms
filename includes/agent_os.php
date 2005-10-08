<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
* AWSTATS BROWSERS DATABASE
* If you want to add a Browser to extend AWStats database detection capabilities,
* you must add an entry in BrowsersSearchIDOrder and in BrowsersHashIDLib.
*/

$osSearchOrder = array (
'windows nt 6\.0',
'windows nt 5\.2',
'windows nt 5\.1',
'windows nt 5\.0',
'winnt4\.0',
'winnt',
'windows 98',
'windows 95',
'win98',
'win95',
'mac os x',
'debian',
'freebsd',
'linux',
'ppc',
'beos',
'sunos',
'apachebench',
'aix',
'irix',
'osf',
'hp-ux',
'netbsd',
'bsdi',
'openbsd',
'gnu',
'unix'
);

$osAlias = array (
'windows nt 6\.0' 	=> 'Windows Longhorn',
'windows nt 5\.2' 	=> 'Windows 2003',
'windows nt 5\.0' 	=> 'Windows 2000',
'windows nt 5\.1'	=> 'Windows XP',
'winnt' 			=> 'Windows NT',
'winnt 4\.0' 		=> 'Windows NT',
'windows 98' 		=> 'Windows 98',
'win98' 			=> 'Windows 98',
'windows 95' 		=> 'Windows 95',
'win95' 			=> 'Windows 95',
'sunos' 			=> 'Sun Solaris',
'freebsd' 			=> 'FreeBSD',
'ppc' 				=> 'Macintosh',
'mac os x' 			=> 'Mac OS X',
'linux' 			=> 'Linux',
'debian' 			=> 'Debian',
'beos' 				=> 'BeOS',
'winnt4\.0' 		=> 'Windows NT 4.0',
'apachebench' 		=> 'ApacheBench',
'aix' 				=> 'AIX',
'irix' 				=> 'Irix',
'osf' 				=> 'DEC OSF',
'hp-ux' 			=> 'HP-UX',
'netbsd' 			=> 'NetBSD',
'bsdi' 				=> 'BSDi',
'openbsd' 			=> 'OpenBSD',
'gnu' 				=> 'GNU/Linux',
'unix' 				=> 'Unknown Unix system'
);
?>