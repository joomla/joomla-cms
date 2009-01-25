<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Banners
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

/**
 * @package		Joomla.Administrator
 * @subpackage	Banners
 */
class BannerViewClient extends JView
{
	function display($tpl = null)
	{
		$app	=& JFactory::getApplication();
		$user	=& JFactory::getUser();
		$model	=& $this->getModel();

		$task = JRequest::getVar('task', '', 'method', 'string');

		JToolBarHelper::title($task == 'add' ? JText::_('Banner Client') . ': <small><small>[ '. JText::_('New') .' ]</small></small>' : JText::_('Banner Client') . ': <small><small>[ '. JText::_('Edit') .' ]</small></small>', 'generic.png');
		JToolBarHelper::save('save');
		JToolBarHelper::apply('apply');
		JToolBarHelper::cancel('cancel');
		JToolBarHelper::help('screen.banners.client.edit');

		$row		=& $this->get('data');
		$isNew		= ($row->cid < 1);

		// fail if checked out not by 'me'
		if ($model->isCheckedOut($user->get('id'))) {
			$msg = JText::sprintf('DESCBEINGEDITTED', JText::_('The banner client'), $row->name);
			$app->redirect('index.php?option=com_banners', $msg);
		}

		// Edit or Create?
		if (!$isNew)
		{
			$model->checkout($user->get('id'));
		}
		else
		{
			// do stuff for new record
			$row->published = 0;
			$row->approved = 0;
		}

		JFilterOutput::objectHTMLSafe($row, ENT_QUOTES, 'extrainfo');

		$this->assignRef('row',			$row);

		parent::display($tpl);
	}
}