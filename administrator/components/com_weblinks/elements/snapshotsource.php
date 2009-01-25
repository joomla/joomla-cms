<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * @package		Joomla.Administrator
 * @subpackage	Weblinks
 */
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
		return JHtml::_('select.genericlist', $sites, ''.$control_name.'['.$name.']', 'class="inputbox"', 'name', 'name', $value, $control_name.$name);
	}
}