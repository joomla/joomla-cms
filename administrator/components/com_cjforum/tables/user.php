<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumTableUser extends JTable
{

	public function __construct (&$db)
	{
		parent::__construct('#__cjforum_users', 'id', $db);
	}
}
