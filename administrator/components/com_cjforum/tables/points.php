<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_PLATFORM') or die();

class CjForumTablePoints extends JTable
{
	public function __construct (JDatabaseDriver $db)
	{
		parent::__construct('#__cjforum_points', 'id', $db);
	}
}
