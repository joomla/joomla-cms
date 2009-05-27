<?php
/**
 * @version		$Id: helper.php 11626 2009-02-15 15:40:39Z kdevine $
 * @package		Joomla.Framework
 * @subpackage	Application
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Application helper functions
 *
 * @static
 * @package		Joomla.Framework
 * @subpackage	Application
 * @since		1.5
 */
class JApplicationHelper
{
	/**
	 * Gets information on a specific client id.  This method will be useful in
	 * future versions when we start mapping applications in the database.
	 *
	 * @access	public
	 * @param	int			$id		A client identifier
	 * @param	boolean		$byName	If True, find the client by it's name
	 * @return	mixed	Object describing the client or false if not known
	 * @since	1.5
	 */
	function &getClientInfo($id = null, $byName = false)
	{
		static $clients;

		// Only create the array if it does not exist
		if (!is_array($clients))
		{
			$obj = new stdClass();

			// Site Client
			$obj->id		= 0;
			$obj->name	= 'site';
			$obj->path	= JPATH_SITE;
			$clients[0] = clone($obj);

			// Administrator Client
			$obj->id		= 1;
			$obj->name	= 'administrator';
			$obj->path	= JPATH_ADMINISTRATOR;
			$clients[1] = clone($obj);

			// Installation Client
			$obj->id		= 2;
			$obj->name	= 'installation';
			$obj->path	= JPATH_INSTALLATION;
			$clients[2] = clone($obj);
		}

		//If no client id has been passed return the whole array
		if (is_null($id)) {
			return $clients;
		}

		// Are we looking for client information by id or by name?
		if (!$byName)
		{
			if (isset($clients[$id])){
				return $clients[$id];
			}
		}
		else
		{
			foreach ($clients as $client)
			{
				if ($client->name == strtolower($id)) {
					return $client;
				}
			}
		}
		$null = null;
		return $null;
	}

	/**
	* Get a path
	*
	* @access public
	* @param string $varname
	* @param string $user_option
	* @return string The requested path
	* @since 1.0
	*/
	function getPath($varname, $user_option=null)
	{
		// check needed for handling of custom/new module xml file loading
		$check = (($varname == 'mod0_xml') || ($varname == 'mod1_xml'));

		if (!$user_option && !$check) {
			$user_option = JRequest::getCmd('option');
		} else {
			$user_option = JFilterInput::clean($user_option, 'path');
		}

		$result = null;
		$name 	= substr($user_option, 4);

		switch ($varname) {
			case 'front':
				$result = JApplicationHelper::_checkPath(DS.'components'.DS. $user_option .DS. $name .'.php', 0);
				break;

			case 'html':
			case 'front_html':
				if (!($result = JApplicationHelper::_checkPath(DS.'templates'.DS. JApplication::getTemplate() .DS.'components'.DS. $name .'.html.php', 0))) {
					$result = JApplicationHelper::_checkPath(DS.'components'.DS. $user_option .DS. $name .'.html.php', 0);
				}
				break;

			case 'toolbar':
				$result = JApplicationHelper::_checkPath(DS.'components'.DS. $user_option .DS.'toolbar.'. $name .'.php', -1);
				break;

			case 'toolbar_html':
				$result = JApplicationHelper::_checkPath(DS.'components'.DS. $user_option .DS.'toolbar.'. $name .'.html.php', -1);
				break;

			case 'toolbar_default':
			case 'toolbar_front':
				$result = JApplicationHelper::_checkPath(DS.'includes'.DS.'HTML_toolbar.php', 0);
				break;

			case 'admin':
				$path 	= DS.'components'.DS. $user_option .DS.'admin.'. $name .'.php';
				$result = JApplicationHelper::_checkPath($path, -1);
				if ($result == null) {
					$path = DS.'components'.DS. $user_option .DS. $name .'.php';
					$result = JApplicationHelper::_checkPath($path, -1);
				}
				break;

			case 'admin_html':
				$path	= DS.'components'.DS. $user_option .DS.'admin.'. $name .'.html.php';
				$result = JApplicationHelper::_checkPath($path, -1);
				break;

			case 'admin_functions':
				$path	= DS.'components'.DS. $user_option .DS. $name .'.functions.php';
				$result = JApplicationHelper::_checkPath($path, -1);
				break;

			case 'class':
				if (!($result = JApplicationHelper::_checkPath(DS.'components'.DS. $user_option .DS. $name .'.class.php'))) {
					$result = JApplicationHelper::_checkPath(DS.'includes'.DS. $name .'.php');
				}
				break;

			case 'helper':
				$path	= DS.'components'.DS. $user_option .DS. $name .'.helper.php';
				$result = JApplicationHelper::_checkPath($path);
				break;

			case 'com_xml':
				$path 	= DS.'components'.DS. $user_option .DS. $name .'.xml';
				$result = JApplicationHelper::_checkPath($path, 1);
				break;

			case 'mod0_xml':
				$path = DS.'modules'.DS. $user_option .DS. $user_option. '.xml';
				$result = JApplicationHelper::_checkPath($path);
				break;

			case 'mod1_xml':
				// admin modules
				$path = DS.'modules'.DS. $user_option .DS. $user_option. '.xml';
				$result = JApplicationHelper::_checkPath($path, -1);
				break;

			case 'plg_xml':
				// Site plugins
				$path 	= DS.'plugins'.DS. $user_option .'.xml';
				$result = JApplicationHelper::_checkPath($path, 0);
				break;

			case 'menu_xml':
				$path 	= DS.'components'.DS.'com_menus'.DS. $user_option .DS. $user_option .'.xml';
				$result = JApplicationHelper::_checkPath($path, -1);
				break;
		}

		return $result;
	}

	function parseXMLInstallFile($path)
	{
		// Read the file to see if it's a valid component XML file
		$xml = & JFactory::getXMLParser('Simple');

		if (!$xml->loadFile($path)) {
			unset($xml);
			return false;
		}

		/*
		 * Check for a valid XML root tag.
		 *
		 * Should be 'install', but for backward compatability we will accept 'mosinstall'.
		 */
		if (!is_object($xml->document) || ($xml->document->name() != 'install' && $xml->document->name() != 'mosinstall')) {
			unset($xml);
			return false;
		}

		$data = array();

		$element = & $xml->document->name[0];
		$data['name'] = $element ? $element->data() : '';
		$data['type'] = $element ? $xml->document->attributes("type") : '';

		$element = & $xml->document->creationDate[0];
		$data['creationdate'] = $element ? $element->data() : JText::_('Unknown');

		$element = & $xml->document->author[0];
		$data['author'] = $element ? $element->data() : JText::_('Unknown');

		$element = & $xml->document->copyright[0];
		$data['copyright'] = $element ? $element->data() : '';

		$element = & $xml->document->authorEmail[0];
		$data['authorEmail'] = $element ? $element->data() : '';

		$element = & $xml->document->authorUrl[0];
		$data['authorUrl'] = $element ? $element->data() : '';

		$element = & $xml->document->version[0];
		$data['version'] = $element ? $element->data() : '';

		$element = & $xml->document->description[0];
		$data['description'] = $element ? $element->data() : '';

		$element = & $xml->document->group[0];
		$data['group'] = $element ? $element->data() : '';

		return $data;
	}

	function parseXMLLangMetaFile($path)
	{
		// Read the file to see if it's a valid component XML file
		$xml = & JFactory::getXMLParser('Simple');

		if (!$xml->loadFile($path)) {
			unset($xml);
			return false;
		}

		/*
		 * Check for a valid XML root tag.
		 *
		 * Should be 'langMetaData'.
		 */
		if ($xml->document->name() != 'metafile') {
			unset($xml);
			return false;
		}

		$data = array();

		$element = & $xml->document->name[0];
		$data['name'] = $element ? $element->data() : '';
		$data['type'] = $element ? $xml->document->attributes("type") : '';

		$element = & $xml->document->creationDate[0];
		$data['creationdate'] = $element ? $element->data() : JText::_('Unknown');

		$element = & $xml->document->author[0];

		$data['author'] = $element ? $element->data() : JText::_('Unknown');

		$element = & $xml->document->copyright[0];
		$data['copyright'] = $element ? $element->data() : '';

		$element = & $xml->document->authorEmail[0];
		$data['authorEmail'] = $element ? $element->data() : '';

		$element = & $xml->document->authorUrl[0];
		$data['authorUrl'] = $element ? $element->data() : '';

		$element = & $xml->document->version[0];
		$data['version'] = $element ? $element->data() : '';

		$element = & $xml->document->description[0];
		$data['description'] = $element ? $element->data() : '';

		$element = & $xml->document->group[0];
		$data['group'] = $element ? $element->group() : '';
		return $data;
	}

	/**
	 * Tries to find a file in the administrator or site areas
	 *
	 * @access private
	 * @param string 	$parth			A file name
	 * @param integer 	$checkAdmin		0 to check site only, 1 to check site and admin, -1 to check admin only
	 * @since 1.5
	 */
	function _checkPath($path, $checkAdmin=1)
	{
		$file = JPATH_SITE . $path;
		if ($checkAdmin > -1 && file_exists($file)) {
			return $file;
		} else if ($checkAdmin != 0) {
			$file = JPATH_ADMINISTRATOR . $path;
			if (file_exists($file)) {
				return $file;
			}
		}

		return null;
	}
}
