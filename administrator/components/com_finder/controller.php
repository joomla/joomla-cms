<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Base controller class for Finder.
 *
 * @since  2.5
 */
class FinderController extends JControllerLegacy
{
	/**
	 * @var    string  The default view.
	 * @since  2.5
	 */
	protected $default_view = 'index';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController  A JController object to support chaining.
	 *
	 * @since	2.5
	 */
	public function display($cachable = false, $urlparams = array())
	{
		include_once JPATH_COMPONENT . '/helpers/finder.php';

		$view   = $this->input->get('view', 'index', 'word');
		$layout = $this->input->get('layout', 'index', 'word');
		$f_id   = $this->input->get('filter_id', null, 'int');

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
