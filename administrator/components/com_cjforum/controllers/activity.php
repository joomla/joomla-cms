<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumControllerActivity extends JControllerForm
{
	public function __construct ($config = array())
	{
		parent::__construct($config);
	}

	protected function postSaveHook (JModelLegacy $model, $validData = array())
	{
		return;
	}
}
