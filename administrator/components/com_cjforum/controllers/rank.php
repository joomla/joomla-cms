<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumControllerRank extends JControllerForm
{
	public function __construct ($config = array())
	{
		parent::__construct($config);
	}

	public function batch ($model = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		// Set the model
		$model = $this->getModel('Rank', '', array());
		
		// Preset the redirect
		$this->setRedirect(JRoute::_('index.php?option=com_cjforum&view=ranks' . $this->getRedirectToListAppend(), false));
		
		return parent::batch($model);
	}

	protected function postSaveHook (JModelLegacy $model, $validData = array())
	{
		return;
	}
}
