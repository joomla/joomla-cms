<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  Application
 */

defined('JPATH_PLATFORM') or die;

/**
 * Application helper functions
 *
 * @static
 * @package		Joomla.Platform
 * @subpackage	Application
 * @since		11.1
 */
class JApplicationHelper
{
	/**
	 * Client information array
	 */
	protected static $_clients = null;

	/**
	 * Return the name of the request component [main component]
	 *
	 * @param	string $default The default option
	 * @return	string Option
	 * @since	11.1
	 */
	public static function getComponentName($default = NULL)
	{
		static $option;

		if ($option) {
			return $option;
		}

		$option = strtolower(JRequest::getCmd('option'));

		if (empty($option)) {
			$option = $default;
		}

		JRequest::setVar('option', $option);
		return $option;
	}

	/**
	 * Gets information on a specific client id.  This method will be useful in
	 * future versions when we start mapping applications in the database.
	 *
	 * This method will return a client information array if called
	 * with no arguments which can be used to add custom application information.
	 *
	 * @param	int			$id		A client identifier
	 * @param	boolean		$byName	If True, find the client by it's name
	 * @return	mixed	Object describing the client or false if not known
	 * @since	11.1
	 */
	public static function getClientInfo($id = null, $byName = false)
	{
		// Only create the array if it does not exist
		if (self::$_clients === null)
		{
			$obj = new stdClass();

			// Site Client
			$obj->id	= 0;
			$obj->name	= 'site';
			$obj->path	= JPATH_SITE;
			self::$_clients[0] = clone $obj;

			// Administrator Client
			$obj->id	= 1;
			$obj->name	= 'administrator';
			$obj->path	= JPATH_ADMINISTRATOR;
			self::$_clients[1] = clone $obj;

			// Installation Client
			$obj->id	= 2;
			$obj->name	= 'installation';
			$obj->path	= JPATH_INSTALLATION;
			self::$_clients[2] = clone $obj;
		}

		// If no client id has been passed return the whole array
		if (is_null($id)) {
			return self::$_clients;
		}

		// Are we looking for client information by id or by name?
		if (!$byName)
		{
			if (isset(self::$_clients[$id])){
				return self::$_clients[$id];
			}
		}
		else
		{
			foreach (self::$_clients as $client)
			{
				if ($client->name == strtolower($id)) {
					return $client;
				}
			}
		}

		return null;
	}

	/**
	 * Adds information for a client.
	 *
	 * @param	mixed	A client identifier either an array or object
	 * @return	boolean	True if the information is added. False on error
	 * @since	11.1
	 */
	public static function addClientInfo($client)
	{
		if (is_array($client)) {
			$client = (object) $client;
		}

		if (!is_object($client)) {
			return false;
		}

		$info = self::getClientInfo();

		if (!isset($client->id)) {
			$client->id = count($info);
		}

		self::$_clients[$client->id] = clone $client;

		return true;
	}

	/**
	* Get a path
	*
	* @param string $varname
	* @param string $user_option
	* @return string The requested path
	* @since 1.0
	*/
	public static function getPath($varname, $user_option=null)
	{
		// check needed for handling of custom/new module xml file loading
		$check = (($varname == 'mod0_xml') || ($varname == 'mod1_xml'));

		if (!$user_option && !$check) {
			$user_option = JRequest::getCmd('option');
		} else {
			$user_option = JFilterInput::getInstance()->clean($user_option, 'path');
		}

		$result = null;
		$name	= substr($user_option, 4);

		switch ($varname) {
			case 'front':
				$result = self::_checkPath(DS.'components'.DS. $user_option .DS. $name .'.php', 0);
				break;

			case 'html':
			case 'front_html':
				if (!($result = self::_checkPath(DS.'templates'.DS. JApplication::getTemplate() .DS.'components'.DS. $name .'.html.php', 0))) {
					$result = self::_checkPath(DS.'components'.DS. $user_option .DS. $name .'.html.php', 0);
				}
				break;

			case 'toolbar':
				$result = self::_checkPath(DS.'components'.DS. $user_option .DS.'toolbar.'. $name .'.php', -1);
				break;

			case 'toolbar_html':
				$result = self::_checkPath(DS.'components'.DS. $user_option .DS.'toolbar.'. $name .'.html.php', -1);
				break;

			case 'toolbar_default':
			case 'toolbar_front':
				$result = self::_checkPath(DS.'includes'.DS.'HTML_toolbar.php', 0);
				break;

			case 'admin':
				$path	= DS.'components'.DS. $user_option .DS.'admin.'. $name .'.php';
				$result = self::_checkPath($path, -1);
				if ($result == null) {
					$path = DS.'components'.DS. $user_option .DS. $name .'.php';
					$result = self::_checkPath($path, -1);
				}
				break;

			case 'admin_html':
				$path	= DS.'components'.DS. $user_option .DS.'admin.'. $name .'.html.php';
				$result = self::_checkPath($path, -1);
				break;

			case 'admin_functions':
				$path	= DS.'components'.DS. $user_option .DS. $name .'.functions.php';
				$result = self::_checkPath($path, -1);
				break;

			case 'class':
				if (!($result = self::_checkPath(DS.'components'.DS. $user_option .DS. $name .'.class.php'))) {
					$result = self::_checkPath(DS.'includes'.DS. $name .'.php');
				}
				break;

			case 'helper':
				$path	= DS.'components'.DS. $user_option .DS. $name .'.helper.php';
				$result = self::_checkPath($path);
				break;

			case 'com_xml':
				$path	= DS.'components'.DS. $user_option .DS. $name .'.xml';
				$result = self::_checkPath($path, 1);
				break;

			case 'mod0_xml':
				$path = DS.'modules'.DS. $user_option .DS. $user_option. '.xml';
				$result = self::_checkPath($path);
				break;

			case 'mod1_xml':
				// admin modules
				$path = DS.'modules'.DS. $user_option .DS. $user_option. '.xml';
				$result = self::_checkPath($path, -1);
				break;

			case 'plg_xml':
				// Site plugins
				$j15path	= DS.'plugins'.DS. $user_option .'.xml';
				$parts = explode(DS, $user_option);
				$j16path = DS.'plugins'.DS. $user_option.DS.$parts[1].'.xml';
				$j15 = self::_checkPath($j15path, 0);
				$j16 = self::_checkPath( $j16path, 0);
				// return 1.6 if working otherwise default to whatever 1.5 gives us
				$result = $j16 ? $j16 : $j15;
				break;

			case 'menu_xml':
				$path	= DS.'components'.DS.'com_menus'.DS. $user_option .DS. $user_option .'.xml';
				$result = self::_checkPath($path, -1);
				break;
		}

		return $result;
	}

	/**
	 * Parse a XML install manifest file.
	 *
	 * @param string $path Full path to xml file.
	 * @return array XML metadata.
	 */
	public static function parseXMLInstallFile($path)
	{
		// Read the file to see if it's a valid component XML file
		if( ! $xml = JFactory::getXML($path))
		{
			return false;
		}

		/*
		 * Check for a valid XML root tag.
		 *
		 * Should be 'install', but for backward compatability we will accept 'extension'.
		 * Languages are annoying and use 'metafile' instead
		 */
		if($xml->getName() != 'install'
		&& $xml->getName() != 'extension'
		&& $xml->getName() != 'metafile')
		{
			unset($xml);
			return false;
		}

		$data = array();

		$data['legacy'] = ($xml->getName() == 'mosinstall' || $xml->getName() == 'install');

		$data['name'] = (string)$xml->name;

		// check if we're a language if so use that
		$data['type'] = $xml->getName() == 'metafile' ? 'language' : (string)$xml->attributes()->type;

		$data['creationDate'] =((string)$xml->creationDate) ? (string)$xml->creationDate : JText::_('Unknown');
		$data['author'] =((string)$xml->author) ? (string)$xml->author : JText::_('Unknown');

		$data['copyright'] = (string)$xml->copyright;
		$data['authorEmail'] = (string)$xml->authorEmail;
		$data['authorUrl'] = (string)$xml->authorUrl;
		$data['version'] = (string)$xml->version;
		$data['description'] = (string)$xml->description;
		$data['group'] = (string)$xml->group;

		return $data;
	}

	public static function parseXMLLangMetaFile($path)
	{
		// Read the file to see if it's a valid component XML file
		$xml = JFactory::getXML($path);

		if( ! $xml)
		{
			return false;
		}

		/*
		 * Check for a valid XML root tag.
		 *
		 * Should be 'langMetaData'.
		 */
		if ($xml->getName() != 'metafile') {
			unset($xml);
			return false;
		}

		$data = array();

		$data['name'] = (string)$xml->name;
		$data['type'] = $xml->attributes()->type;

		$data['creationDate'] =((string)$xml->creationDate) ? (string)$xml->creationDate : JText::_('JLIB_UNKNOWN');
		$data['author'] =((string)$xml->author) ? (string)$xml->author : JText::_('JLIB_UNKNOWN');

		$data['copyright'] = (string)$xml->copyright;
		$data['authorEmail'] = (string)$xml->authorEmail;
		$data['authorUrl'] = (string)$xml->authorUrl;
		$data['version'] = (string)$xml->version;
		$data['description'] = (string)$xml->description;
		$data['group'] = (string)$xml->group;

		return $data;
	}

	/**
	 * Tries to find a file in the administrator or site areas
	 *
	 * @param string	A file name
	 * @param integer	0 to check site only, 1 to check site and admin, -1 to check admin only
	 * @since   11.1
	 */
	protected static function _checkPath($path, $checkAdmin=1)
	{
		$file = JPATH_SITE . $path;
		if ($checkAdmin > -1 && file_exists($file)) {
			return $file;
		}
		else if ($checkAdmin != 0)
		{
			$file = JPATH_ADMINISTRATOR . $path;
			if (file_exists($file)) {
				return $file;
			}
		}

		return null;
	}
}
