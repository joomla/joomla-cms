<?php
/**
 * @version		$Id: toolbar.newsfeeds.html.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Administrator
 * @subpackage	Newsfeeds
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	Newsfeeds
 */
class TOOLBAR_newsfeeds
{
	function _DEFAULT()
	{
		JToolBarHelper::title(JText::_('Newsfeed Manager'));
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::deleteList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		JToolBarHelper::preferences('com_newsfeeds','400');
		JToolBarHelper::help('screen.newsfeeds');
	}

	function _EDIT($edit)
	{
		$cid = JRequest::getVar('cid', array(0), '', 'array');
		JArrayHelper::toInteger($cid, array(0));

		$text 	= ($edit ? JText::_('Edit') : JText::_('New'));

		JToolBarHelper::title(JText::_('Newsfeed').': <small><small>[ '. $text.' ]</small></small>');
		JToolBarHelper::save();
		JToolBarHelper::apply();
		if ($edit) {
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel('cancel', 'Close');
		} else {
			JToolBarHelper::cancel();
		}
		JToolBarHelper::help('screen.newsfeeds.edit');
	}
}