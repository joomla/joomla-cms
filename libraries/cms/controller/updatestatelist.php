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
class JControllerUpdatestatelist extends JControllerCmsbase
{
	/*
	 * Prefix for the view and model classes
	 *
	 * @var  string
	 */
	public $prefix;

	/*
	 * Optional values needed for the model
	 *
	 *  @var  array
	 */
	protected  $options = array('published' => 1, 'unpublished' => 0, 'archived' =>2,
				'trashed' => -2, 'reported' => -3);

	/**
	 * @return  mixed  A rendered view or true
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		// Get the application
		$app = $this->getApplication();

		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JInvalid_Token'));

		$componentFolder = $this->input->getWord('option', 'com_content');
		$viewName     = $this->input->getWord('view', 'articles');

		$ids = $this->input->get('cid', array(), 'array');

		if (empty($ids))
		{
			JError::raiseWarning(500, JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'));
		}
		else
		{
			$modelClassName = ucfirst($this->prefix) . 'Model' . ucfirst($viewName);
			$model = new $modelClassName;
			$newState = $this->options[$this->option];

			// Check in the items.
			$app->enqueueMessage(JText::plural('JLIB_CONTROLLER_N_ITEMS_PUBLISHED', $model->publish($ids,$newState)));
		}

		$app->redirect('index.php?option=' . $this->input->get('option', 'com_cpanel'));

	}
}