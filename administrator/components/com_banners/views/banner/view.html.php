<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Banners
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
* @package		Joomla
* @subpackage	Banners
*/
class BannerViewBanner extends JView
{
	function display($tpl = null)
	{
		$user 	=& JFactory::getUser();
		$model	=& $this->getModel();

		// Set toolbar items for the page
		$edit		= JRequest::getVar('edit',true);
		$text = !$edit ? JText::_('New') : JText::_('Edit');
		JToolBarHelper::title(  JText::_('Banner').': <small><small>[ ' . $text.' ]</small></small>', 'generic.png');
		JToolBarHelper::save('save');
		JToolBarHelper::apply('apply');
		JToolBarHelper::cancel('cancel');
		JToolBarHelper::help('screen.banners.edit');

		//get the banner
		$item	=& $this->get('data');

		// fail if checked out not by 'me'
		if ($model->isCheckedOut($user->get('id'))) {
			$msg = JText::sprintf('DESCBEINGEDITTED', JText::_('The banner'), $item->name);
			JFactory::getApplication()->redirect('index.php?option=com_banners', $msg);
		}

		// Edit or Create?
		if ($edit)
		{
			$model->checkout($user->get('id'));
		}
		else
		{
			// initialise new record
			$item->showBanner = 1;
		}

		// build the html select list for ordering
		$order_query = 'SELECT ordering AS value, name AS text'
			. ' FROM #__banner'
			. ' WHERE catid = ' . (int) $item->catid
			. ' ORDER BY ordering';

		//clean data
		JFilterOutput::objectHTMLSafe($item, ENT_QUOTES, 'custombannercode');

		$this->assignRef('item',		$item);
		$this->assignRef('order_query',	$order_query);

		parent::display($tpl);
	}
}