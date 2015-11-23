<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

// Base this model on the backend version.
require_once JPATH_ADMINISTRATOR . '/components/com_cjforum/models/activity.php';

class CjForumModelActivityForm extends CjForumModelActivity
{
	public $typeAlias = 'com_cjforum.activity';

	protected function populateState ()
	{
		$app = JFactory::getApplication();
		
		// Load state from the request.
		$pk = $app->input->getInt('t_id');
		$this->setState('activity.id', $pk);
		
		$return = $app->input->get('return', null, 'base64');
		$this->setState('return_page', base64_decode($return));
		
		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);
		
		$this->setState('layout', $app->input->getString('layout'));
	}
}
