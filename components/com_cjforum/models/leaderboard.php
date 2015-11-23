<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();
jimport( 'joomla.application.component.modellist' );
require_once JPATH_COMPONENT.'/models/users.php';

class CjForumModelLeaderboard extends CjForumModelUsers
{
	public function __construct ($config = array())
	{
		parent::__construct($config);
	}

	protected function populateState ($ordering = null, $direction = null)
	{
		parent::populateState('karma', 'desc');
		
		$this->setState('list.ordering', 'karma');
		$this->setState('list.direction', 'desc');
		$this->setState('list.limit', 20);
	}
}