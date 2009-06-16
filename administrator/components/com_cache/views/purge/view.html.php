<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Cache
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the Cache component
 *
 * @static
 * @package		Joomla.Administrator
 * @subpackage	Cache
 * @since 1.6
 */
class CacheViewPurge extends JView
{
	public function display($tpl = null)
	{
		$this->_setToolbar();
		parent::display($tpl);
	}

	protected function _setToolbar()
	{
		JSubMenuHelper::addEntry(JText::_('Back to Clean Cache Admin'), 'index.php?option=com_cache', false);

		JToolBarHelper::title(JText::_('Cache Manager - Purge Cache Admin'), 'checkin.png');
		JToolBarHelper::custom('purge', 'delete.png', 'delete_f2.png', 'Purge expired', false);
		JToolBarHelper::help('screen.cache');
	}
}