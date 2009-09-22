<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport( 'joomla.application.component.controller' );

/**
 * The Menu Item Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @since		1.6
 */
class MenusControllerItem extends JController
{
	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		// Register proxy tasks.
		$this->registerTask('save2copy',	'save');
		$this->registerTask('save2new',		'save');
		$this->registerTask('apply',		'save');
	}

	/**
	 * Dummy method to redirect back to standard controller
	 *
	 * @return	void
	 */
	public function display()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_menus', false));
	}

	/**
	 * Method to add a new menu item.
	 *
	 * @return	void
	 */
	public function add()
	{
		// Initialize variables.
		$app = &JFactory::getApplication();

		// Clear the row edit information from the session.
		$app->setUserState('com_menus.edit.item.id',	null);
		$app->setUserState('com_menus.edit.item.data',	null);
		$app->setUserState('com_menus.edit.item.type',	null);
		$app->setUserState('com_menus.edit.item.link',	null);

		// Check if we are adding for a particular menutype
		$menuType = $app->getUserStateFromRequest($this->_context.'.filter.menutype', 'menutype', 'mainmenu');

		// Redirect to the edit screen.
		$this->setRedirect(JRoute::_('index.php?option=com_menus&view=item&layout=edit&menutype='.$menuType, false));
	}

	/**
	 * Method to edit an existing menu item.
	 *
	 * @return	void
	 */
	public function edit()
	{
		// Initialize variables.
		$app	= &JFactory::getApplication();
		$pks	= JRequest::getVar('cid', array(), '', 'array');

		// Get the id of the group to edit.
		$id		=  (empty($pks) ? JRequest::getInt('item_id') : (int) array_pop($pks));

		// Get the menu item model.
		$model	= &$this->getModel('Item');

		// Check that this is not a new item.
		if ($id > 0)
		{
			$item = $model->getItem($id);

			// If not already checked out, do so.
			if ($item->checked_out == 0)
			{
				if (!$model->checkout($id))
				{
					// Check-out failed, go back to the list and display a notice.
					$message = JText::sprintf('JError_Checkout_failed', $model->getError());
					//$this->setRedirect('index.php?option=com_menus&view=item&item_id='.$id, $message, 'error');
					return false;
				}
			}
		}

		// Push the new row id into the session.
		$app->setUserState('com_menus.edit.item.id',	$id);
		$app->setUserState('com_menus.edit.item.data',	null);
		$app->setUserState('com_menus.edit.item.type',	null);
		$app->setUserState('com_menus.edit.item.link',	null);

		$this->setRedirect('index.php?option=com_menus&view=item&layout=edit');

		return true;
	}

	/**
	 * Method to cancel an edit
	 *
	 * Checks the item in, sets item ID in the session to null, and then redirects to the list page.
	 *
	 * @return	void
	 */
	public function cancel()
	{
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialize variables.
		
		$app	= &JFactory::getApplication();
		// Get the previous menu item id (if any) and the current menu item id.
		$previousId	= (int) $app->getUserState('com_content.edit.item.id');
		
		$model	= &$this->getModel('Item');




		// If rows ids do not match, checkin previous row.
		if (!$model->checkin($previousId)) {
		// Check-in failed, go back to the menu item and display a notice.
			$message = JText::sprintf('JError_Checkin_failed', $model->getError());
			$this->setRedirect('index.php?option=com_content&view=item&layout=edit', $message, 'error');
			return false;		
			
		}

				// Clear the row edit information from the session.
		$app->setUserState('com_menus.edit.item.id',	null);
		$app->setUserState('com_menus.edit.item.data',	null);
		$app->setUserState('com_menus.edit.item.type',	null);
		$app->setUserState('com_menus.edit.item.link',	null);

	// Redirect to the list screen.
		$this->setRedirect(JRoute::_('index.php?option=com_menus&view=items', false));
	}
	/**
	 * Method to save a menu item.
	 *
	 * @return	void
	 */
	public function save()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialize variables.
		$app	= &JFactory::getApplication();
		$model	= &$this->getModel('Item');
		$task	= $this->getTask();

		// Get the posted values from the request.
		$iData	= JRequest::getVar('jform', array(), 'post', 'array');
		$pData	= JRequest::getVar('jformparams', array(), 'post', 'array');
		$map	= JRequest::getVar('menuid', array(), 'post', 'array');

		// Populate the row id from the session.
		$iData['id'] = (int) $app->getUserState('com_menus.edit.item.id');

		// The save2copy task needs to be handled slightly differently.
		if ($task == 'save2copy')
		{
			// Check-in the original row.
			if (!$model->checkin())
			{
				// Check-in failed, go back to the item and display a notice.
				$message = JText::sprintf('JError_Checkin_saved', $model->getError());
			//	$this->setRedirect('index.php?option=com_menus&view=item&layout=edit', $message, 'error');
				return false;
			}

			// Reset the ID and then treat the request as for Apply.
			$iData['id']	= 0;
			$task			= 'apply';
		}

		// Validate the posted data.
		// This post is made up of two forms, one for the item and one for params.
		$itemForm	= &$model->getForm();
		if (!$itemForm) {
			JError::raiseError(500, $model->getError());
			return false;
		}
		$iData	= $model->validate($itemForm, $iData);

		$paramsForm	= &$model->getParamsForm($iData['type'], $iData['link']);
		if (!$paramsForm) {
			JError::raiseError(500, $model->getError());
			return false;
		}
		$pData	= $model->validate($paramsForm, $pData);

		// Check for the special 'request' entry.
		if ($iData['type'] == 'component' && isset($pData['request']) && is_array($pData['request']) && !empty($pData['request']))
		{
			// Parse the submitted link arguments.
			$args = array();
			parse_str(parse_url($iData['link'], PHP_URL_QUERY), $args);

			// Merge in the user supplied request arguments.
			$args = array_merge($args, $pData['request']);
			$iData['link'] = 'index.php?'.http_build_query($args);
			unset($pData['request']);
		}

		// Params are validated so add them to the item data.
		$iData['params'] = $pData;

		// Push the menu id map back into the array
		$iData['map'] = &$map;

		// Check for validation errors.
		if ($iData === false)
		{
			// Get the validation messages.
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if (JError::isError($errors[$i])) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'notice');
				}
				else {
					$app->enqueueMessage($errors[$i], 'notice');
				}
			}

			// Save the data in the session.
			$app->setUserState('com_menus.edit.item.data', $iData);

			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_menus&view=item&layout=edit', false));
			return false;
		}

		// Attempt to save the data.
		if (!$model->save($iData))
		{
			// Save the data in the session.
			$app->setUserState('com_menus.edit.item.data', $iData);

			// Redirect back to the edit screen.
			$this->setMessage(JText::sprintf('JError_Save_failed', $model->getError()), 'notice');
		//	$this->setRedirect(JRoute::_('index.php?option=com_menus&view=item&layout=edit', false));
			return false;
		}

		// Save succeeded, check-in the row.
		if (!$model->checkin())
		{
			// Check-in failed, go back to the row and display a notice.
			$message = JText::sprintf('JError_Checkin_saved', $model->getError());
		//	$this->setRedirect('index.php?option=com_menus&view=item&layout=edit', $message, 'error');
			return false;
		}

		$this->setMessage(JText::_('JController_Save_success'));

		// Redirect the user and adjust session state based on the chosen task.
		switch ($task)
		{
			case 'apply':
				// Set the row data in the session.
				$app->setUserState('com_menus.edit.item.id',	$model->getState('item.id'));
				$app->setUserState('com_menus.edit.item.data',	null);
				$app->setUserState('com_menus.edit.item.type',	null);
				$app->setUserState('com_menus.edit.item.link',	null);

				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_('index.php?option=com_menus&view=item&layout=edit', false));
				break;

			case 'save2new':
				// Clear the row id and data in the session.
				$app->setUserState('com_menus.edit.item.id',	null);
				$app->setUserState('com_menus.edit.item.data',	null);
				$app->setUserState('com_menus.edit.item.type',	null);
				$app->setUserState('com_menus.edit.item.link',	null);

				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_('index.php?option=com_menus&view=item&layout=edit', false));
				break;

			default:
				// Clear the row id and data in the session.
				$app->setUserState('com_menus.edit.item.id',	null);
				$app->setUserState('com_menus.edit.item.data',	null);
				$app->setUserState('com_menus.edit.item.type',	null);
				$app->setUserState('com_menus.edit.item.link',	null);

				// Redirect to the list screen.
				$this->setRedirect(JRoute::_('index.php?option=com_menus&view=items', false));
				break;
		}
	}

	function setType()
	{
		// Initialize variables.
		$app	= &JFactory::getApplication();
		
		// Get the type.
		$type = JRequest::getVar('type');
		$type = json_decode(base64_decode($type));
		$title = isset($type->title) ? $type->title : null;
		if ($title != 'alias' && $title != 'separator' && $title != 'url') {
			$title = 'component';
		}
		$app->setUserState('com_menus.edit.item.type',	$title);
		if ($title=='component'){
			if (isset($type->request)) {

				$app->setUserState('com_menus.edit.item.link', 'index.php?option='.  $type->request->option.'&view='. $type->request->view);

				//	$app->setUserState('com_menus.edit.item.id',	$model->getState('item.id'));
				//	$app->setUserState('com_menus.edit.item.data',	null);
				//$app->setUserState('com_menus.edit.item.type',	$type->type);
				//	$app->setUserState('com_menus.edit.item.link',	null);
			}
		}
		//If the type is alias you just need the item id from the menu item referenced.
		else if ($title=='alias'){
				$app->setUserState('com_menus.edit.item.link', 'index.php?Itemid=');

				//	$app->setUserState('com_menus.edit.item.id',	$model->getState('item.id'));
				//	$app->setUserState('com_menus.edit.item.data',	null);
				//$app->setUserState('com_menus.edit.item.type',	$type->type);
				//	$app->setUserState('com_menus.edit.item.link',	null);
		}
		//else if ($title=='url'){
			//	$app->setUserState('com_menus.edit.item.link', null );

				//	$app->setUserState('com_menus.edit.item.id',	$model->getState('item.id'));
				//	$app->setUserState('com_menus.edit.item.data',	null);
				//$app->setUserState('com_menus.edit.item.type',	$type->type);
				//	$app->setUserState('com_menus.edit.item.link',	null);
		//}
		
		$this->type=$type;
		$this->setRedirect('index.php?option=com_menus&view=item&layout=edit');
	}

	/**
	 * Method to run batch opterations.
	 *
	 * @return	void
	 */
	function batch()
	{
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialize variables.
		$app	= &JFactory::getApplication();
		$model	= &$this->getModel('Item');
		$vars	= JRequest::getVar('batch', array(), 'post', 'array');
		$cid	= JRequest::getVar('cid', array(), 'post', 'array');

		// Preset the redirect
		$this->setRedirect('index.php?option=com_menus&view=items');

		// Attempt to run the batch operation.
		if ($model->batch($vars, $cid))
		{
			$this->setMessage(JText::_('Menus_Batch_success'));
			return true;
		}
		else
		{
			$this->setMessage(JText::_(JText::sprintf('Menus_Error_Batch_failed', $model->getError())));
			return false;
		}
	}
}
