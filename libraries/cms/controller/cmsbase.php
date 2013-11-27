<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Joomla.Libraries
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Base Display Controller
 *
 * @package     Joomla.Libraries
 * @subpackage  controller
 * @since       3.2
*/
class JControllerCmsbase extends JControllerBase
{
	// Standard values for the exploded controller input
	const CONTROLLER_PREFIX = 0;
	const CONTROLLER_ACTIVITY = 1;
	const CONTROLLER_VIEW_FOLDER = 2;
	const CONTROLLER_OPTION = 3;
	const CONTROLLER_CORE_OPTION = 2;

	/*
	 * Prefix for the view and model classes
	 *
	 * @var  string
	 */
	public $prefix;

	/*
	 * Permission needed for the action. Defaults to most restrictive
	*
	* @var  string
	*/
	public $permission = 'core.admin';

	/**
	 * @return  mixed  A rendered view or true
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		// Get the application
		$this->app = $this->getApplication();

		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JInvalid_Token'));

		$this->componentFolder = $this->input->getWord('option', 'com_content');
		$this->viewName     = $this->input->getWord('view', 'articles');

		return $this;

	}
}
