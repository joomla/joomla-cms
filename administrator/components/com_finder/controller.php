<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Base controller class for Finder.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 * @since       2.5
 */
class FinderController extends JController
{
	/**
	 * @var		string	The default view.
	 * @since	2.5
	 */
	protected $default_view = 'index';

	/**
	 * Method to display a view.
	 *
	 * @return	void
	 *
	 * @since	2.5
	 */
	public function display()
	{
		include_once JPATH_COMPONENT.'/helpers/finder.php';

		// Load the submenu.
		FinderHelper::addSubmenu(JRequest::getWord('view', 'index'));

		$view		= JRequest::getWord('view', 'index');
		$layout 	= JRequest::getWord('layout', 'index');
		$id			= JRequest::getInt('id');
		$f_id		= JRequest::getInt('filter_id');

		// Check for edit form.
		if ($view == 'filter' && $layout == 'edit' && !$this->checkEditId('com_finder.edit.filter', $f_id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $f_id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_finder&view=filters', false));

			return false;
		}

		parent::display();

		return $this;
	}
}
