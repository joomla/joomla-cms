<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Base Controller for updating the state of items
 *
 * @package     Joomla.Libraries
 * @subpackage  Controller
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
	 * Permission needed for the action
	*
	* @var  string
	*/
	public $permission = 'core.edit.state';


	/*
	 * Optional values needed for the model
	 * Note that we need to support some values twice for legacy reasons.
	 *
	 *  @var  array
	 */
	public  $stateOptions = array('published' => 1, 'unpublished' => 0, 'archived' => 2,
				'trashed' => -2, 'reported' => -3, 'publish' => 1, 'unpublish' => 0);

	/**
	 * Method to update the state of a record.
	 *
	 * @return  boolean  True if controller finished execution, false if the controller did not
	 *                   finish execution. A controller might return false if some precondition for
	 *                   the controller to run has not been satisfied.
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	public function execute()
	{
		parent::execute();

		$ids = $this->input->get('cid', array(), 'array');

		if (empty($ids))
		{
			throw new RuntimeException(JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'), 500);
		}
		else
		{
			try
			{
				$model = $this->getModel();
			}
			catch (RuntimeException $e)
			{
				throw new RuntimeException($e->getMessage(), $e->getCode());
			}

			$newState = $this->stateOptions[$this->options[parent::CONTROLLER_CORE_OPTION]];

			// Access check.
			if (!JFactory::getUser()->authorise($this->permission, $model->getState('component.option')))
			{
				$this->app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

				return;
			}

			// Check in the items.
			$this->app->enqueueMessage(JText::plural('JLIB_CONTROLLER_N_ITEMS_PUBLISHED', $model->publish($ids, $newState)));
		}

		$this->app->redirect('index.php?option=' . $this->input->get('option', 'com_cpanel'));

	}
}
