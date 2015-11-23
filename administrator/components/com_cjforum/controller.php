<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumController extends JControllerLegacy
{

	protected $default_view = 'dashboard';

	public function display ($cachable = false, $urlparams = false)
	{
		$view = $this->input->get('view', 'dashboard');
		$layout = $this->input->get('layout');
		$id = $this->input->getInt('id');
		
		// Check for edit form.
		if ($view == 'topic' && $layout == 'edit' && ! $this->checkEditId('com_cjforum.edit.topic', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_cjforum&view=topics', false));
			
			return false;
		}
		
		parent::display();
		
		return $this;
	}
}
