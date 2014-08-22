<?php
/**
 * @package     Joomla.CMS
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('JPATH_PLATFORM') or die();

/**
 * Base class for a Joomla Administrator Controller.
 * It implements basic methods such as: Create, Update, Delete, Publis, Check-in, Checkout..
 *
 * @package Joomla.CMS
 * @subpackage Controller
 * @since 3.5
 */
class JCmsControlleAdmin extends JCmsController
{

	/**
	 * The URL view item variable.
	 *
	 * @var string
	 */
	protected $viewItem;

	/**
	 * The URL view list variable.
	 *
	 * @var string
	 */
	protected $viewList;

	/**
	 * Context, used to store user session data
	 *
	 * @var string
	 */
	protected $context;

	/**
	 * Constructor.
	 *
	 * @param array $config An optional associative array of configuration settings.
	 *        	     	
	 * @see JCmsControlleAdmin
	 */
	public function __construct(JInput $input = null, array $config = array())
	{
		parent::__construct($input, $config);
		
		$this->context = $this->option . '.' . $this->name;
		
		if (isset($config['view_item']))
		{
			$this->viewItem = $config['view_item'];
		}
		else
		{
			$this->viewItem = $this->name;
		}
		
		if (isset($config['view_list']))
		{
			$this->viewList = $config['view_list'];
		}
		else
		{
			$this->viewList = JCmsInflector::pluralize($this->viewItem);
		}
		// Register tasks mapping
		$this->registerTask('apply', 'save');
		$this->registerTask('save2new', 'save');
		$this->registerTask('save2copy', 'save');
		$this->registerTask('unpublish', 'publish');
		$this->registerTask('archive', 'publish');
		$this->registerTask('trash', 'publish');
		$this->registerTask('orderup', 'reorder');
		$this->registerTask('orderdown', 'reorder');
	}

	/**
	 * Display Form allows adding a new record
	 */
	public function add()
	{
		$model = $this->getModel($this->name, array('default_model_class' => 'JCmsModelAdmin', 'ignore_request' => true));
		if ($model->canAdd($this->input->getArray()))
		{
			// Clear the record edit information from the session.
			$this->app->setUserState($this->context . '.data', null);
			$this->input->set('view', $this->viewItem);
			$this->input->set('layout', 'edit');
			$this->display();
		}
		else
		{
			$this->setMessage(JText::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'), 'error');
			$this->setRedirect(JRoute::_($this->getViewListUrl(), false));
			return false;
		}
	}

	/**
	 * Display Form allows editing record
	 */
	public function edit()
	{
		$model = $this->getModel($this->name, array('default_model_class' => 'JCmsModelAdmin'));
		$id = $model->getState()->id;
		if (!$id)
		{
			$cid = $model->getState()->cid;
			$id = (int) $cid[0];
		}
		if (!$model->canEdit($id))
		{
			$this->setMessage(JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'), 'error');
			$this->setRedirect(JRoute::_($this->getViewListUrl(), false));
			return false;
		}
		// Checkout the record before allowing edit
		if ($model->checkin && !$model->checkout($id))
		{
			// Check-out failed, display a notice but allow the user to see the record.
			$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKOUT_FAILED', $model->getError()), 'error');
			$this->setRedirect(JRoute::_($this->getViewItemUrl($id), false));
			return false;
		}
		$this->holdEditId($this->context, $id);
		$this->app->setUserState($this->context . '.data', null);
		$this->input->set('view', $this->viewItem);
		$this->input->set('layout', 'edit');
		$this->display();
	}

	/**
	 * Method to save a record.
	 *
	 * @return boolean True if successful, false otherwise.
	 *        
	 */
	public function save()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$model = $this->getModel($this->name, array('default_model_class' => 'JCmsModelAdmin'));
		$task = $this->getTask();
		$data = $this->input->post->get('jform', array(), 'array');
		$id = $this->input->getInt('id');
		$data['id'] = $id;
		// ACL check
		if (!$model->canSave($data))
		{
			$this->setMessage(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');
			$this->setRedirect(JRoute::_($this->getViewListUrl(), false));
			return false;
		}
		
		// The save2copy task needs to be handled slightly differently.
		if ($task == 'save2copy')
		{
			// Check-in the original row.
			if ($model->checkin && $model->checkin($id) === false)
			{
				// Check-in failed. Go back to the item and display a notice.
				$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()), 'error');
				$this->setRedirect(JRoute::_($this->getViewItemUrl($id), false));
				return false;
			}
			// Reset the ID and then treat the request as for Apply.
			$data['id'] = 0;
			$task = 'apply';
		}
		
		// Validate the posted data.
		// Sometimes the form needs some posted data, such as for plugins and modules.
		$form = $model->getForm($data, false);
		if (!$form)
		{
			$this->app->enqueueMessage($model->getError(), 'error');
			return false;
		}
		// Test whether the data is valid.
		$validData = $model->validate($form, $data);
		
		// Check for validation errors.
		if ($validData === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();
			
			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$this->app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$this->app->enqueueMessage($errors[$i], 'warning');
				}
			}
			
			// Save the data in the session.
			$this->app->setUserState($this->context . '.data', $data);
			
			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_($this->getViewItemUrl($id), false));
			
			return false;
		}
		
		if (!isset($validData['tags']))
		{
			$validData['tags'] = null;
		}
		
		// Attempt to save the data.
		if (!$model->save($validData, $this->input))
		{
			// Save the data in the session.
			$this->app->setUserState($this->context . '.data', $validData);
			// Redirect back to the edit screen.
			$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'error');
			$this->setRedirect(JRoute::_($this->getViewItemUrl($id), false));
			return false;
		}
		
		// Save succeeded, so check-in the record.
		if ($model->checkin && $model->checkin($validData['id']) === false)
		{
			// Save the data in the session.
			$this->app->setUserState($this->context . '.data', $validData);
			// Check-in failed, so go back to the record and display a notice.
			$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()), 'error');
			$this->setRedirect(JRoute::_($this->getViewItemUrl($id), false));
			return false;
		}
		
		if ($this->app->isSite() && $id == 0)
		{
			$langSuffix = '_SUBMIT_SAVE_SUCCESS';
		}
		else
		{
			$langSuffix = '_SAVE_SUCCESS';
		}
		$this->setMessage(JText::_(JFactory::getLanguage()->hasKey($this->languagePrefix . $langSuffix) ? $this->languagePrefix : 'JLIB_APPLICATION' . $langSuffix));
				
		// Redirect the user and adjust session state based on the chosen task.
		switch ($task)
		{
			case 'apply':
				// Set the record data in the session.
				$id = $model->getState()->id;
				$this->holdEditId($this->context, $id);
				$this->app->setUserState($this->context . '.data', null);
				$model->checkout($id);
				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_($this->getViewItemUrl($id), false));
				break;
			
			case 'save2new':
				// Clear the record id and data from the session.
				$this->releaseEditId($this->context, $id);
				$this->app->setUserState($this->context . '.data', null);
				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_($this->getViewItemUrl($id), false));
				break;
			
			default:
				// Clear the record id and data from the session.
				$this->releaseEditId($this->context, $id);
				$this->app->setUserState($this->context . '.data', null);
				// Redirect to the list screen.
				$this->setRedirect(JRoute::_($this->getViewListUrl(), false));
				break;
		}
		return true;
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @param string $key The name of the primary key of the URL variable.
	 *        	
	 * @return boolean True if access level checks pass, false otherwise.
	 *        
	 */
	public function cancel()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$model = $this->getModel($this->name, array('default_model_class' => 'JCmsModelAdmin', 'ignore_request' => true));
		$id = $this->input->getInt('id');
		// Attempt to check-in the current record.
		if ($id && $model->checkin && $model->checkin($id) === false)
		{
			// Check-in failed, go back to the record and display a notice.
			$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()), 'error');
			$this->setRedirect(JRoute::_($this->getViewItemUrl($id), false));
			return false;
		}
		// Clean the session data and redirect.
		$this->releaseEditId($this->context, $id);
		$this->app->setUserState($this->context . '.data', null);
		$this->setRedirect(JRoute::_($this->getViewListUrl(), false));
		return true;
	}

	/**
	 * Method to save the submitted ordering values for records.
	 *
	 * @return boolean True on success
	 *        
	 */
	public function saveorder()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		// Get the input
		$cid = $this->input->post->get('cid', array(), 'array');
		$order = $this->input->post->get('order', array(), 'array');
		
		// Make sure there is atleast one items selected
		if (count($cid) == 0)
		{
			$this->setMessage(JText::_($this->languagePrefix . '_ERROR_NO_ITEMS_SELECTED'), 'warning');
			$this->setRedirect(JRoute::_($this->getViewListUrl(), false));
			return false;
		}
		
		// Sanitize the input
		JArrayHelper::toInteger($cid);
		JArrayHelper::toInteger($order);
		
		// Get the model
		$model = $this->getModel($this->name, array('default_model_class' => 'JCmsModelAdmin', 'ignore_request' => true));
		
		// Save the ordering
		$return = $model->saveorder($cid, $order);
		
		if ($return === false)
		{
			// Reorder failed
			$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_REORDER_FAILED', $model->getError()), 'error');
			$this->setRedirect(JRoute::_($this->getViewListUrl(), false));
			return false;
		}
		else
		{
			// Reorder succeeded
			$this->setMessage(JText::_('JLIB_APPLICATION_SUCCESS_ORDERING_SAVED'));
			$this->setRedirect(JRoute::_($this->getViewListUrl(), false));
			return true;
		}
	}

	/**
	 * Changes the order of one or more records.
	 *
	 * @return boolean True on success
	 *        
	 */
	public function reorder()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$cid = $this->input->post->get('cid', array(), 'array');
		
		//Make sure there is atleast one item selected
		if (count($cid) == 0)
		{
			$this->setMessage(JText::_($this->languagePrefix . '_ERROR_NO_ITEMS_SELECTED'), 'warning');
			$this->setRedirect(JRoute::_($this->getViewListUrl(), false));
			return false;
		}
		
		$inc = ($this->getTask() == 'orderup') ? -1 : 1;
		$model = $this->getModel($this->name, array('default_model_class' => 'JCmsModelAdmin', 'ignore_request' => true));
		$return = $model->reorder($cid, $inc);
		if ($return === false)
		{
			// Reorder failed.
			$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_REORDER_FAILED', $model->getError()), 'error');
			$this->setRedirect(JRoute::_($this->getViewListUrl(), false));
			return false;
		}
		else
		{
			// Reorder succeeded.
			$this->setMessage(JText::_('JLIB_APPLICATION_SUCCESS_ITEM_REORDERED'), 'message');
			$this->setRedirect(JRoute::_($this->getViewListUrl(), false));
			return true;
		}
	}

	/**
	 * Check in of one or more records.
	 *
	 * @return boolean True on success
	 */
	public function checkin()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$cid = $this->input->post->get('cid', array(), 'array');
		
		//Make sure there is atleast one item selected
		if (count($cid) == 0)
		{
			$this->setMessage(JText::_($this->languagePrefix . '_ERROR_NO_ITEMS_SELECTED'), 'warning');
			$this->setRedirect(JRoute::_($this->getViewListUrl(), false));
			return false;
		}
		
		//Santinize the input
		JArrayHelper::toInteger($cid);
		
		$model = $this->getModel($this->name, array('default_model_class' => 'JCmsModelAdmin'));
		$return = $model->checkin($cid);
		if ($return === false)
		{
			// Checkin failed.
			$message = JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError());
			$this->setRedirect(JRoute::_($this->getViewListUrl(), false), $message, 'error');
			return false;
		}
		else
		{
			// Checkin succeeded.
			$message = JText::plural($this->languagePrefix . '_N_ITEMS_CHECKED_IN', count($cid));
			$this->setRedirect(JRoute::_($this->getViewListUrl(), false), $message);
			return true;
		}
	}

	/**
	 * Removes an item.
	 *
	 * @return void
	 *
	 */
	public function delete()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		// Get items to remove from the request.
		$cid = $this->input->get('cid', array(), 'array');
		
		// Make sure there is at least one item selected
		if (count($cid) == 0)
		{
			$this->setMessage(JText::_($this->languagePrefix . '_ERROR_NO_ITEMS_SELECTED'), 'warning');
			$this->setRedirect(JRoute::_($this->getViewListUrl(), false));
			return false;
		}
		
		JArrayHelper::toInteger($cid);
		$model = $this->getModel($this->name, array('default_model_class' => 'JCmsModelAdmin', 'ignore_request' => true));
		// Remove the items.
		if ($model->delete($cid))
		{
			$this->setMessage(JText::plural($this->languagePrefix . '_N_ITEMS_DELETED', count($cid)));
		}
		else
		{
			$this->setMessage($model->getError(), 'error');
		}
		
		$this->setRedirect(JRoute::_($this->getViewListUrl(), false));
	}

	/**
	 * Method to publish a list of items
	 *
	 * @return void
	 */
	public function publish()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));
		// Get items to publish from the request.
		$cid = $this->input->get('cid', array(), 'array');
		$data = array('publish' => 1, 'unpublish' => 0, 'archive' => 2, 'trash' => -2, 'report' => -3);
		$task = $this->getTask();
		$value = JArrayHelper::getValue($data, $task, 0, 'int');
		
		// Make sure there is at least one item selected
		if (count($cid) == 0)
		{
			$this->setMessage(JText::_($this->languagePrefix . '_ERROR_NO_ITEMS_SELECTED'), 'warning');
			$this->setRedirect(JRoute::_($this->getViewListUrl(), false));
			return false;
		}
		
		//Santinize the input		
		JArrayHelper::toInteger($cid);
		
		// Get the model.
		$model = $this->getModel($this->name, array('default_model_class' => 'JCmsModelAdmin', 'ignore_request' => true));
		try
		{
			$model->publish($cid, $value);
			if ($value == 1)
			{
				$ntext = $this->languagePrefix . '_N_ITEMS_PUBLISHED';
			}
			elseif ($value == 0)
			{
				$ntext = $this->languagePrefix . '_N_ITEMS_UNPUBLISHED';
			}
			elseif ($value == 2)
			{
				$ntext = $this->languagePrefix . '_N_ITEMS_ARCHIVED';
			}
			else
			{
				$ntext = $this->languagePrefix . '_N_ITEMS_TRASHED';
			}
			$this->setMessage(JText::plural($ntext, count($cid)));
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');
		}
		$extension = $this->input->get('extension');
		$extensionURL = ($extension) ? '&extension=' . $extension : '';
		$this->setRedirect(JRoute::_($this->getViewListUrl() . $extensionURL, false));
	}

	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return void
	 */
	public function saveOrderAjax()
	{
		// Get the input
		$cid = $this->input->post->get('cid', array(), 'array');
		$order = $this->input->post->get('order', array(), 'array');
		// Sanitize the input
		JArrayHelper::toInteger($cid);
		JArrayHelper::toInteger($order);
		// Get the model
		$model = $this->getModel($this->name, array('default_model_class' => 'JCmsModelAdmin', 'ignore_request' => true));
		// Save the ordering
		$return = $model->saveorder($cid, $order);
		if ($return)
		{
			echo "1";
		}
		// Close the application
		$this->app->close();
	}

	/**
	 * Get url of the page which display list of records
	 *
	 * @return string
	 */
	public function getViewListUrl()
	{
		return 'index.php?option=' . $this->option . '&view=' . $this->viewList;
	}

	/**
	 * Get url of the page which allow adding/editing a record
	 *
	 * @param int $recordId        	
	 * @param string $urlVar        	
	 * @return string
	 */
	public function getViewItemUrl($recordId = null)
	{
		$url = 'index.php?option=' . $this->option . '&view=' . $this->viewItem;
		if ($recordId)
		{
			$url .= '&id=' . $recordId;
		}
		return $url;
	}

	/**
	 * Method to add a record ID to the edit list.
	 *
	 * @param string $context The context for the session storage.
	 *        	
	 * @param integer $id The ID of the record to add to the edit list.
	 *        		         	
	 * @return void
	 */
	protected function holdEditId($context, $id)
	{
		$values = (array) $this->app->getUserState($context . '.id');
		// Add the id to the list if non-zero.
		if (!empty($id))
		{
			array_push($values, (int) $id);
			$values = array_unique($values);
			$this->app->setUserState($context . '.id', $values);
		}
	}

	/**
	 * Method to check whether an ID is in the edit list.
	 *
	 * @param string $context The context for the session storage.
	 *        	
	 * @param integer $id The ID of the record to add to the edit list.
	 *        	
	 * @return void
	 *
	 */
	protected function releaseEditId($context, $id)
	{
		$values = (array) $this->app->getUserState($context . '.id');
		// Do a strict search of the edit list values.
		$index = array_search((int) $id, $values, true);
		if (is_int($index))
		{
			unset($values[$index]);
			$this->app->setUserState($context . '.id', $values);
		}
	}
}
