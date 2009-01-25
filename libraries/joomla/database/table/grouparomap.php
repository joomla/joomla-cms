<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

require_once dirname(__FILE__).DS.'_groupmap.php';

/**
 * Table object for Group to User map.
 *
 * @package		Joomla.Framework
 * @subpackage	Table
 */
class JTableGroupAroMap extends JTable_GroupMap
{
	var $aro_id = null;

	/**
	 * @var	string The section type
	 * @protected
	 * @final
	 */
	var $_type = 'aro';
}
