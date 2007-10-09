<?php
/**
* @version		$Id: classes.php 9198 2007-10-08 19:39:40Z jinx $
* @package		Joomla.Legacy
* @subpackage	1.5
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

// Register legacy classes for autoloading
JLoader::register('JToolbarHelper' , JPATH_ADMINISTRATOR.DS.'includes'.DS.'toolbar.php');

/**
 * Legacy class, use {@link JToolbarHelper} instead
 *
 * @deprecated	As of version 1.5
 * @package	Joomla.Legacy
 * @subpackage	1.5
 */
class mosMenuBar extends JToolbarHelper
{
	/**
	* @deprecated As of Version 1.5
	*/
	function startTable()
	{
		return;
	}

	/**
	* @deprecated As of Version 1.5
	*/
	function endTable()
	{
		return;
	}

	/**
	 * Default $task has been changed to edit instead of new
	 *
	 * @deprecated As of Version 1.5
	 */
	function addNew($task = 'new', $alt = 'New')
	{
		parent::addNew($task, $alt);
	}

	/**
	 * Default $task has been changed to edit instead of new
	 *
	 * @deprecated As of Version 1.5
	 */
	function addNewX($task = 'new', $alt = 'New')
	{
		parent::addNew($task, $alt);
	}

	/**
	 * Deprecated
	 *
	 * @deprecated As of Version 1.5
	 */
	function saveedit()
	{
		parent::save('saveedit');
	}

}