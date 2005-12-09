<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Libraries
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

class JLoader
{
    /**
    * Loads a class from specified directories.
    *
    * @param string $name The class name to look for.
    * @param string|array $dirs Search these directories for the class.
    * @return void
    * @since 1.1
    */
   function import( $filePath ) {
		global  $mosConfig_absolute_path; //for backwards compilance

		$parts = explode( '.', $filePath );

		$base =  dirname( __FILE__ );

		if(array_pop( $parts ) == '*')
		{
			$path = $base . DIRECTORY_SEPARATOR . implode( DIRECTORY_SEPARATOR, $parts );

			if (!is_dir( $path )) {
				return; //TODO : throw error
			}

			$dir = dir( $path );
			while ($file = $dir->read()) {
				if (ereg( '\.php$', $file )) {
					include_once $path . DIRECTORY_SEPARATOR . $file;
				}
			}
			$dir->close();
		} else {
			$path = str_replace( '.', DIRECTORY_SEPARATOR, $filePath );
			$found = false;
			foreach (array( '.php', '.class.php', '.lib.php' ) as $suffix) {
				if (file_exists( $base . DIRECTORY_SEPARATOR . $path . $suffix )) {
					$found = true;
					break;
				}
			}

			if ($found) {
				include_once $base . DIRECTORY_SEPARATOR . $path . $suffix;
			} else {
				return;  //TODO : throw error
			}
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
       JLoader::import($class);
       $obj = new $class($options);
       return $obj;
   }
}

/**
 * Intelligent file importer
 * @param string A dot syntax path
 * @param boolean True to use require_once, false to use require
 */
function jimport( $path ) {
	JLoader::import($path);
}
?>
