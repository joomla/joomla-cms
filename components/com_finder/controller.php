<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Finder Component Controller.
 *
 * @package     Joomla.Site
 * @subpackage  com_finder
 * @since       2.5
 */
class FinderController extends JController
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached. [optional]
	 * @param   array    $urlparams  An array of safe url parameters and their variable types,
	 *                               for valid values see {@link JFilterInput::clean()}. [optional]
	 *
	 * @return  JController  This object is to support chaining.
	 *
	 * @since   1.6
	 */
	public function display($cachable = false, $urlparams = array())
	{
		// Initialise variables.
		$input = JFactory::getApplication()->input;
		$cachable = true;
		$user = JFactory::getUser();

		// Set the default view name and format from the Request.
		$viewName = $input->get('view', 'search', 'word');
		$input->set('view', $viewName);

		if ($user->get('id') || ($_SERVER['REQUEST_METHOD'] == 'POST' && $vName = 'search'))
		{
			$cachable = false;
		}

		$safeurlparams = array(
			'id' => 'INT',
			'limit' => 'INT',
			'limitstart' => 'INT',
			'filter_order' => 'CMD',
			'filter_order_Dir' => 'CMD',
			'lang' => 'CMD'
		);

		return parent::display($cachable, $safeurlparams);
	}
}
