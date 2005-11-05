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
define( '_MOS_MAMBO_INCLUDED', 1 );

class JBase 
{
    /**
    * Loads a class from specified directories.
    *
    * @param string $name The class name to look for.
    * @param string|array $dirs Search these directories for the class.
    * @return void
    * @since 1.1
    */
   function import($name, $dirs = null) 
   {
	   global $mosConfig_absolute_path;
	   
       // pre-empt loading if the class exists
       if (class_exists($name)) {
           return;
       }
       // if no dirs specified, look at the filename
       // (this only works if underscores are allowed)
       if (! $dirs) {
           $dirs = str_replace('.', DIRECTORY_SEPARATOR, $name);
       }
	   
	   $path = 
       // look for it in the various dirs
       $found = false;
       $file = false;
       foreach ((array) $dirs as $dir) {
           $file = $mosConfig_absolute_path . DIRECTORY_SEPARATOR . $dir . '.php';
           if (JBase::isReadable($file)) {
               $found = true;
               break;
           }
       }
       // did we find it?
       if (! $found) {
           $message = "File for class '$name' not found or not readable.";
           //throw new $exception($message);
       }
       
       // load the file, see if the class existed in it
       include_once($file);
       if (! class_exists($name)) {
           $message = "File '$file' loaded, but class '$name' not defined.";
           //thrown new $exception($message);
       }
       
       return;
   }

   /**
    * A common object factory.
    *     * Assumes that the class constructor takes only one parameter, an
    * associative array of construction options.
    *     * Attempts to load the class automatically.
    *
    * @param string $class The class name to instantiate.
    * @param array $options An associative array of options (default null).
    * @return object An object instance.
    */
   function &factory($class, $options = null) {
       JBase::import($class);
       $obj = new $class($options);
       return $obj;
   }
   /**
    * The equivalent of is_readable(), but uses the include_path.
    *
    * @param string $file The file to look for.
    * @return bool True if the file was found and readable, false if not.
    */
   function isReadable($file) {
       $fp = @fopen($file, 'r', true);
       $ok = ($fp) ? true : false;
       @fclose($fp);
       return $ok;
   }
}

/**
 * Intelligent file importer
 * @param string A dot syntax path
 * @param boolean True to use require_once, false to use require
 */
function jimport( $path ) {
	JBase::import($path);
}

if (phpversion() < '4.2.0') {
	jimport('libraries.joomla.compat.php41x' );
}
if (phpversion() < '4.3.0') {
	jimport('libraries.joomla.compat.php42x' );
}
if (in_array( 'globals', array_keys( array_change_key_case( $_REQUEST, CASE_LOWER ) ) ) ) {
	die( 'Fatal error.  Global variable hack attempted.' );
}
if (in_array( '_post', array_keys( array_change_key_case( $_REQUEST, CASE_LOWER ) ) ) ) {
	die( 'Fatal error.  Post variable hack attempted.' );
}
if (version_compare( phpversion(), '5.0' ) < 0) {
	jimport('libraries.joomla.compat.php50x' );
}

@set_magic_quotes_runtime( 0 );

if (@$mosConfig_error_reporting === 0) {
	error_reporting( 0 );
} else if (@$mosConfig_error_reporting > 0) {
	error_reporting( $mosConfig_error_reporting );
}

// experimenting

jimport( 'libraries.joomla.database.mysql' );

require_once( $mosConfig_absolute_path . '/includes/phpmailer/class.phpmailer.php' );
require_once( $mosConfig_absolute_path . '/includes/phpInputFilter/class.inputfilter.php' );

jimport( 'libraries.joomla.classes.object' );
jimport( 'libraries.joomla.version' );
jimport( 'libraries.joomla.functions' );
jimport( 'libraries.joomla.classes' );
jimport( 'libraries.joomla.models' );
jimport( 'libraries.joomla.html' );
jimport( 'libraries.joomla.factory' );
jimport( 'libraries.joomla.files' );
jimport( 'libraries.joomla.xml' );


/** @global $database */
$database =& JFactory::getDatabase();

/** @global $acl */
$acl =& JFactory::getACL();

/** @global $_MAMBOTS */
$_MAMBOTS = new mosMambotHandler();

/** @global $_VERSION */
$_VERSION = new JVersion();

//TODO : implement mambothandler class as singleton, add getBotHandler to JFactory

//TODO : implement editor functionality as a class
jimport( 'libraries.joomla.editor' );


//TODO : implement mambothandler class as singleton, add getVersion to JFactory
jimport( 'libraries.joomla.legacy' );
?>
