<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

class JElementSnapshotSource extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Snapshot';

	function fetchElement($name, $value, &$node, $control_name)
	{
		JModel::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_weblinks'.DS.'models');
		$model =& JModel::getInstance('snapshotsources','WeblinksModel');
		$sites = $model->getData();
		return JHTML::_('select.genericlist', $sites, ''.$control_name.'['.$name.']', 'class="inputbox"', 'name', 'name', $value, $control_name.$name );
	}
}