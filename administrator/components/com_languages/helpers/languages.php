<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Languages component helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @since		1.6
 */
class LanguagesHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	The name of the active view.
	 */
	public static function addSubmenu($vName)
	{
		JSubMenuHelper::addEntry(
			JText::_('COM_LANGUAGES_SUBMENU_INSTALLED_SITE'),
			'index.php?option=com_languages&view=installed&client=0',
			$vName == 'installed'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_LANGUAGES_SUBMENU_INSTALLED_ADMINISTRATOR'),
			'index.php?option=com_languages&view=installed&client=1',
			$vName == 'installed'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_LANGUAGES_SUBMENU_CONTENT'),
			'index.php?option=com_languages&view=languages',
			$vName == 'languages'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_LANGUAGES_SUBMENU_OVERRIDES'),
			'index.php?option=com_languages&view=overrides',
			$vName == 'overrides'
		);
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return	JObject
	 */
	public static function getActions()
	{
		$user		= JFactory::getUser();
		$result		= new JObject;
		$assetName	= 'com_languages';

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;
	}

  public function parseFile($filename)
  {
    jimport('joomla.filesystem.file');

    if(!JFile::exists($filename))
    {
      return array();
    }

		// Capture hidden PHP errors from the parsing.
		$version = phpversion();
		$php_errormsg	= null;
		$track_errors	= ini_get('track_errors');
		ini_set('track_errors', true);

		if($version >= '5.3.1')
    {
			$contents = file_get_contents($filename);
			$contents = str_replace('_QQ_', '"\""', $contents);
			$strings = @parse_ini_string($contents);
		}
		else
    {
			$strings = @parse_ini_file($filename);

			if($version == '5.3.0' && is_array($strings))
      {
				foreach($strings as $key => $string)
				{
					$strings[$key]=str_replace('_QQ_', '"', $string);
				}
			}
		}

    return $strings;
  }

  static function filterKey($value)
  {
    $filter = JFilterInput::getInstance(null, null, 1, 1);

    return strtoupper($filter->clean($value, 'cmd'));
  }

  static function filterText($value)
  {
    $filter = JFilterInput::getInstance(null, null, 1, 1);

    return str_replace('"', '"_QQ_"', $filter->clean($value));
  }
}
