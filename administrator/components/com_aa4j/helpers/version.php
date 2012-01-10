<?php
/**
 * @version		1.6.0.40 helpers/version.php
 * @package		AA4J 
 * @subpackage	com_aa4j 
 * @since		1.6.0
 *
 * @author		Alikon <info@alikonweb.it>
 * @link		http://www.alikonweb.it
 * @copyright	Copyright (C) 2011 Alikonweb. All Rights Reserved
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL v3
 * AA4J is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
 
// no direct access
defined('_JEXEC') or die('Restricted access.');

class aa4jVersion
{
	/** @public static string Product */
	public static $PRODUCT	= 'AA4J';
	/** @public static int Main Release Level */
	public static $RELEASE	= '1.8';
	/** @public static int Sub Release Level */
	public static $DEV_LEVEL	= '0';
	/** @public static string Development Status */
	public static $DEV_STATUS	= '';
	/** @public static int build Number */
	public static $BUILD		= '00';
	/** @public static string Codename */
	public static $CODENAME	= 'Fortwentyfive';
	/** @public static string Copyright Text */
	public static $COPYRIGHT	= 'Copyright &copy; 2010-2012 Alikonweb <a href="http://www.alikonweb.it" title="alikonweb.it"><img src="components/com_aa4j/alikonlogo_16.png" alt="alikonweb.it" /></a>. All rights reserved.';
	/** @public static string License */
	public static $LICENSE	= '<a href="http://www.gnu.org/licenses/gpl-3.0.html">GNU GPL v3</a>';	
	/** @public static string URL */
	public static $URL		= '<a href="http://www.alikonweb.it/aa4j.html">AA4J</a> is Free Software released under the GNU General Public License.';

	/**
	 * Method to get the long version information.
	 *
	 * @return	string	Long format version.
	 */
	public static function getLongVersion()
	{
		return self::$RELEASE .'.'. self::$DEV_LEVEL .' '
			. (self::$DEV_STATUS ? ' '.self::$DEV_STATUS : '')
			. ' build ' . self::$BUILD
			.' [ '.self::$CODENAME .' ] '
			;
	}

	/**
	 * Method to get the full version information.
	 *
	 * @return	string	version.
	 */
	public static function getFullVersion()
	{
		return self::$RELEASE 
			.'.'.self::$DEV_LEVEL
			. (self::$DEV_STATUS ? '-'.self::$DEV_STATUS : '')
			.'.'.self::$BUILD;
	}

	/**
	 * Method to get the short version information.
	 *
	 * @return	string	Short version format.
	 */
	public static function getShortVersion() {
		return self::$RELEASE .'.'. self::$DEV_LEVEL;
	}
		/**
	 * Method to get the short version information.
	 *
	 * @return	string	Short version format.
	 */
	public static function getPluginLink($plugintype,$pluginame) {
		  $pluginid=0;
		  $db = JFactory::getDbo();			
			$query	= $db->getQuery(true);
			$query->select('extension_id');			
			$query->from('#__extensions');
			$query->where('type='.$db->Quote("plugin").' AND folder ='.$db->Quote($plugintype).' AND element='.$db->Quote($pluginame));
			$db->setQuery($query);
			
			$pluginid = $db->loadResult();

			if ($db->getErrorNum()) {
				$this->setError($db->getErrorMsg());
				return false;
			}
			
		
		return $pluginid;
	}
}
