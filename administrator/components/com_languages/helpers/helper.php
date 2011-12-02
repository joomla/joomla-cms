<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-2.0/JG/trunk/administrator/components/com_joomgallery/helpers/helper.php $
// $Id: helper.php 3378 2011-10-07 18:37:56Z aha $
/****************************************************************************************\
**   JoomGallery 2                                                                      **
**   By: JoomGallery::ProjectTeam                                                       **
**   Copyright (C) 2008 - 2011  JoomGallery::ProjectTeam                                **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                            **
**   Released under GNU GPL Public License                                              **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look                       **
**   at administrator/components/com_joomgallery/LICENSE.TXT                            **
\****************************************************************************************/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

/**
 * JoomGallery Global Helper for the Backend
 *
 * @static
 * @package JoomGallery
 * @since 1.5.5
 */
class OverriderHelper
{
  /**
   * Add managers to the sub-menu
   *
   * @return  void
   * @since  2.0
   */
  public static function addSubmenu()
  {
    $current_client = JFactory::getApplication()->getUserState('com_overrider.client', 'site');

    $clients = array( 'site'          => JText::_('COM_OVERRIDER_SUBMENU_SITE'),
                      'administrator' => JText::_('COM_OVERRIDER_SUBMENU_ADMINISTRATOR')
                    );

    foreach($clients as $client => $title)
    {
      JSubMenuHelper::addEntry( $title,
                                'index.php?option=com_overrider&client='.$client,
                                $client == $current_client
                              );
    }
  }

  /**
   * Returns a list of the actions that can be performed
   *
   * @param   string  $type The type of the content to check
   * @param   int     $id   The ID of the content (category or image)
   * @return  JObject An object holding the results of the check
   * @since   2.0
   */
  public static function getActions()
  {
    static $cache = null;

    if(!empty($cache))
    {
      return $cache;
    }

    $user   = JFactory::getUser();
    $result = new JObject();

    $actions = array('core.admin', 'core.manage', 'core.create', 'core.edit', 'core.delete');

    foreach($actions as $action)
    {
      $result->set($action, $user->authorise($action, 'com_overrider'));
    }

    // Store the result for better performance
    $cache = $result;

    return $result;
  }

  public function parse($client = 'site', $language = 'en-GB')
  {
    $filename = constant('JPATH_'.strtoupper($client)).DS.'language'.DS.'overrides'.DS.$language.'.override.ini';

    return OverriderHelper::parseFile($filename);
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

  static function filter($value)
  {
    return str_replace('"', '"_QQ_"', $value);
  }
}