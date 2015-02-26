<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file contains post-installation message handling for the checks if the installation is
 * affected by the issue with content languages access in 3.4.0
 */

defined('_JEXEC') or die;

/**
 * Checks if the installation is affected by the issue with content languages access in 3.4.0
 *
 * @see     https://github.com/joomla/joomla-cms/pull/6172
 * @see     https://github.com/joomla/joomla-cms/pull/6194
 *
 * @return  bool
 *
 * @since   3.4.1
 */
function admin_postinstall_languageaccess340_condition()
{
	$db    = $this->getDbo();
	$query = $db->getQuery(true)
		->select($db->quoteName('access'))
		->from($db->quoteName('#__languages'))
		->where($db->quoteName('access') . " = " . $db->quote('0'));
	$db->setQuery($query);
	$db->execute();
	$numRows = $db->getNumRows();
	
	if (isset($numRows) && $numRows != 0)
	{
	  // We have rows here so we have at minumum
	  // one row with access set to 0
	  return true;
	}
	else
	{
	  // All good the query retrun nothing.
	  return false;
	}
}

/**
 * Updating the language access '0' to '1' as action.
 *
 * @return  void
 *
 * @since   3.4.1
 */
function admin_postinstall_languageaccess340_action()
{
	$query = $db->getQuery(true)
		->update($db->quoteName('#__languages'))
		->set($db->quoteName('access') . " = " . $db->quote('1'))
		->where($db->quoteName('access') . " = " . $db->quote('0'));
	$db->setQuery($query);
	$result = $db->execute();

}
