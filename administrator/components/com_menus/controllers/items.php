<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * The Menu Item Controller
 *
 * @since  1.6
 */
class MenusControllerItems extends JControllerAdmin
{
	/**
	 * Constructor
	 *
	 * @param   array  $config  Optional configuration array
	 *
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerTask('unsetDefault',	'setDefault');
		$this->registerTask('unsetInheritable',	'setInheritable');
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Item', $prefix = 'MenusModel', $config = array())
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	/**
	 * Rebuild the nested set tree.
	 *
	 * @return  bool  False on failure or error, true on success.
	 *
	 * @since   1.6
	 */
	public function rebuild()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$this->setRedirect('index.php?option=com_menus&view=items');

		$model = $this->getModel();

		if ($model->rebuild())
		{
			// Reorder succeeded.
			$this->setMessage(JText::_('COM_MENUS_ITEMS_REBUILD_SUCCESS'));

			return true;
		}
		else
		{
			// Rebuild failed.
			$this->setMessage(JText::sprintf('COM_MENUS_ITEMS_REBUILD_FAILED'), 'error');

			return false;
		}
	}

	/**
	 * Save the manual order inputs from the menu items list view
	 *
	 * @return      void
	 *
	 * @see         JControllerAdmin::saveorder()
	 * @deprecated  4.0
	 */
	public function saveorder()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		try
		{
			JLog::add(
				sprintf('%s() is deprecated. Function will be removed in 4.0.', __METHOD__),
				JLog::WARNING,
				'deprecated'
			);
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

		// Get the arrays from the Request
		$order = $this->input->post->get('order', null, 'array');
		$originalOrder = explode(',', $this->input->getString('original_order_values'));

		// Make sure something has changed
		if (!($order === $originalOrder))
		{
			parent::saveorder();
		}
		else
		{
			// Nothing to reorder
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));

			return true;
		}
	}

	/**
	 * Method to set the inheritable property for a list of items
	 *
	 * @return  void
	 *
	 * @since   2017-02-17
	 */
	public function setInheritable()
	{
		// Check for request forgeries
		JSession::checkToken('request') or die(JText::_('JINVALID_TOKEN'));
	
		$app = JFactory::getApplication();
	
		// Get items to set Inheritable from the request.
		$cid   = $this->input->get('cid', array(), 'array');
		$data  = array('setInheritable' => 1, 'unsetInheritable' => 0);
		$task  = $this->getTask();
		$value = JArrayHelper::getValue($data, $task, 0, 'int');
	
		if (empty($cid))
		{
			JError::raiseWarning(500, JText::_($this->text_prefix . '_NO_ITEM_SELECTED'));
		}
		else
		{
			// Get the model.
			$model = $this->getModel();
	
			// Make sure the item ids are integers
			JArrayHelper::toInteger($cid);
	
			// set the items Inheritable.
			if (!$model->setInheritable($cid, $value))
			{
				JError::raiseWarning(500, $model->getError());
			}
			else
			{
				if ($value == 1)
				{
					$ntext = 'COM_MENUS_ITEMS_SET_INHERITABLE';
				}
				else
				{
					$ntext = 'COM_MENUS_ITEMS_UNSET_INHERITABLE';
				}
	
				$this->setMessage(JText::plural($ntext, count($cid)));
			}
		}
	
		$this->setRedirect(
				JRoute::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_list
						. '&menutype=' . $app->getUserState('com_menus.items.menutype'), false
						)
				);
	}

	/**
	 * Method to set the home property for a list of items
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function setDefault()
	{
		// Check for request forgeries
		JSession::checkToken('request') or die(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();

		// Get items to publish from the request.
		$cid   = $this->input->get('cid', array(), 'array');
		$data  = array('setDefault' => 1, 'unsetDefault' => 0);
		$task  = $this->getTask();
		$value = ArrayHelper::getValue($data, $task, 0, 'int');

		if (empty($cid))
		{
			JError::raiseWarning(500, JText::_($this->text_prefix . '_NO_ITEM_SELECTED'));
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			$cid = ArrayHelper::toInteger($cid);

			// Publish the items.
			if (!$model->setHome($cid, $value))
			{
				JError::raiseWarning(500, $model->getError());
			}
			else
			{
				if ($value == 1)
				{
					$ntext = 'COM_MENUS_ITEMS_SET_HOME';
				}
				else
				{
					$ntext = 'COM_MENUS_ITEMS_UNSET_HOME';
				}

				$this->setMessage(JText::plural($ntext, count($cid)));
			}
		}

		$this->setRedirect(
				JRoute::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_list
						. '&menutype=' . $app->getUserState('com_menus.items.menutype'), false
						)
				);
	}

	/**
	 * Method to publish a list of items
	 *
	 * @return  void
	 *
	 * @since   3.6.0
	 */
	public function publish()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to publish from the request.
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');
		$data = array('publish' => 1, 'unpublish' => 0, 'trash' => -2, 'report' => -3);
		$task = $this->getTask();
		$value = ArrayHelper::getValue($data, $task, 0, 'int');

		if (empty($cid))
		{
			try
			{
				JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
			}
			catch (RuntimeException $exception)
			{
				JFactory::getApplication()->enqueueMessage(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), 'warning');
			}
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			$cid = ArrayHelper::toInteger($cid);

			// Publish the items.
			try
			{
				$model->publish($cid, $value);
				$errors = $model->getErrors();

				if ($value == 1)
				{
					if ($errors)
					{
						$app = JFactory::getApplication();
						$app->enqueueMessage(JText::plural($this->text_prefix . '_N_ITEMS_FAILED_PUBLISHING', count($cid)), 'error');
					}
					else
					{
						$ntext = $this->text_prefix . '_N_ITEMS_PUBLISHED';
					}
				}
				elseif ($value == 0)
				{
					$ntext = $this->text_prefix . '_N_ITEMS_UNPUBLISHED';
				}
				else
				{
					$ntext = $this->text_prefix . '_N_ITEMS_TRASHED';
				}

				$this->setMessage(JText::plural($ntext, count($cid)));
			}
			catch (Exception $e)
			{
				$this->setMessage($e->getMessage(), 'error');
			}
		}

		$this->setRedirect(
			JRoute::_(
				'index.php?option=' . $this->option . '&view=' . $this->view_list . '&menutype=' .
				JFactory::getApplication()->getUserState('com_menus.items.menutype'),
				false
			)
		);
	}

	/**
	 * Check in of one or more records.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.6.0
	 */
	public function checkin()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$ids = JFactory::getApplication()->input->post->get('cid', array(), 'array');

		$model = $this->getModel();
		$return = $model->checkin($ids);

		if ($return === false)
		{
			// Checkin failed.
			$message = JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError());
			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list
					. '&menutype=' . JFactory::getApplication()->getUserState('com_menus.items.menutype'),
					false
				),
				$message,
				'error'
			);

			return false;
		}
		else
		{
			// Checkin succeeded.
			$message = JText::plural($this->text_prefix . '_N_ITEMS_CHECKED_IN', count($ids));
			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list
					. '&menutype=' . JFactory::getApplication()->getUserState('com_menus.items.menutype'),
					false
				),
				$message
			);

			return true;
		}
	}
}
