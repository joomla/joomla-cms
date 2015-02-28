<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JControllerStateReorder extends JControllerState
{
	protected function updateRecordState($model, $cid)
	{
		$cids = $this->getIds();
		$model->saveorder($cids);
		$this->addMessage(JText::_('BABELU_LIB_CONTROLLER_MESSAGE_ORDERING_SAVED'));
	}
}