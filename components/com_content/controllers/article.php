<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * @package		Joomla.Site
 * @subpackage	com_content
 */
class ContentControllerArticle extends JControllerForm
{
	/**
	 * @since	1.6
	 */
	protected $view_item = 'form';

	/**
	 * @since	1.6
	 */
	protected $view_list = 'categories';

	/**
	 * Constructor
	 *
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('apply',		'save');
		$this->registerTask('save2new',		'save');
		$this->registerTask('save2copy',	'save');
	}

	/**
	 * Method override to check if you can add a new record.
	 *
	 * @param	array	An array of input data.
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	protected function allowAdd($data = array())
	{
		// Initialise variables.
		$user		= JFactory::getUser();
		$categoryId	= JArrayHelper::getValue($data, 'catid', JRequest::getInt('catid'), 'int');
		$allow		= null;

		if ($categoryId) {
			// If the category has been passed in the data or URL check it.
			$allow	= $user->authorise('core.create', 'com_content.category.'.$categoryId);
		}

		if ($allow === null) {
			// In the absense of better information, revert to the component permissions.
			return parent::allowAdd();
		}
		else {
			return $allow;
		}
	}

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param	array	$data	An array of input data.
	 * @param	string	$key	The name of the key for the primary key.
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// Initialise variables.
		$recordId	= (int) isset($data[$key]) ? $data[$key] : 0;
		$user		= JFactory::getUser();
		$userId		= $user->get('id');
		$asset		= 'com_content.article.'.$recordId;

		// Check general edit permission first.
		if ($user->authorise('core.edit', $asset)) {
			return true;
		}

		// Fallback on edit.own.
		// First test if the permission is available.
		if ($user->authorise('core.edit.own', $asset)) {
			// Now test the owner is the user.
			$ownerId	= (int) isset($data['created_by']) ? $data['created_by'] : 0;
			if (empty($ownerId) && $recordId) {
				// Need to do a lookup from the model.
				$record		= $this->getModel()->getItem($recordId);

				if (empty($record)) {
					return false;
				}

				$ownerId = $record->created_by;
			}

			// If the owner matches 'me' then do the test.
			if ($ownerId == $userId) {
				return true;
			}
		}

		// Since there is no asset tracking, revert to the component permissions.
		return parent::allowEdit($data, $key);
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param	string	$name	The model name. Optional.
	 * @param	string	$prefix	The class prefix. Optional.
	 * @param	array	$config	Configuration array for model. Optional.
	 *
	 * @return	object	The model.
	 * @since	1.5
	 */
	public function &getModel($name = 'form', $prefix = '', $config = array())
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	protected function getReturnPage()
	{
		$app		= JFactory::getApplication();
		$context	= "$this->option.edit.$this->context";

		if (!($return = $app->getUserState($context.'.return'))) {
			$return = JRequest::getVar('return', base64_encode(JURI::base()));
		}

		$return = JFilterInput::getInstance()->clean($return, 'base64');
		$return = base64_decode($return);

		if (!JURI::isInternal($return)) {
			$return = JURI::base();
		}

		return $return;
	}

	protected function setReturnPage()
	{
		$app		= JFactory::getApplication();
		$context	= "$this->option.edit.$this->context";

		$return = JRequest::getVar('return', null, 'default', 'base64');

		$app->setUserState($context.'.return', $return);
	}

	/**
	 * Method to add a new record.
	 *
	 * @return	boolean	True if the article can be added, false if not.
	 * @since	1.6
	 */
	public function add()
	{
		$app		= JFactory::getApplication();
		$context	= "$this->option.edit.$this->context";

		// Access check
		if (!$this->allowAdd()) {
			JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));

			return false;
		}

		// Clear the record edit information from the session.
		$app->setUserState($context.'.data',	null);

		// Clear the return page.
		// TODO: We should be including an optional 'return' variable in the URL.
		$this->setReturnPage();

		// ItemID required on redirect for correct Template Style
		$redirect = 'index.php?option=com_content&view=form&layout=edit';
		if (JRequest::getInt('Itemid') != 0) {
			$redirect .= '&Itemid='.JRequest::getInt('Itemid');
		}

		$this->setRedirect($redirect);

		return true;
	}

	/**
	 * Method to edit a object
	 *
	 * Sets object ID in the session from the request, checks the item out, and then redirects to the edit page.
	 *
	 * @return	boolean	True if the record can be edited, false if not.
	 */
	public function edit()
	{
		// Initialise variables.
		$app		= JFactory::getApplication();
		$context	= "$this->option.edit.$this->context";
		$ids		= JRequest::getVar('cid', array(), '', 'array');

		// Get the id of the group to edit.
		$id =  (int) (empty($ids) ? JRequest::getInt('id') : array_pop($ids));

		// Access check
		if (!$this->allowEdit(array('id' => $id))) {
			JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));

			return false;
		}

		// Get the menu item model.
		$model = $this->getModel();

		// Check that this is not a new item.

		if ($id > 0) {
			$item = $model->getItem($id);

			// If not already checked out, do so.
			if ($item->checked_out == 0) {
				if (!$model->checkout($id)) {
					// Check-out failed, go back to the list and display a notice.
					$message = JText::sprintf('JLIB_APPLICATION_ERROR_CHECKOUT_FAILED', $model->getError());
					$this->setRedirect('index.php?option=com_content&view=article&item_id='.$id, $message, 'error');

					return false;
				}
			}
		}

		// Check-out succeeded, register the ID for editing.
		$this->holdEditId($context, $id);
		$app->setUserState($context.'.data',	null);

		$this->setReturnPage();

		// ItemID required on redirect for correct Template Style
		$redirect = 'index.php?option=com_content&view=form&layout=edit&id='.$id;

		if (JRequest::getInt('Itemid') != 0) {
			$redirect .= '&Itemid='.JRequest::getInt('Itemid');
		}

		$this->setRedirect($redirect);

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
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app		= JFactory::getApplication();
		$model		= $this->getModel();
		$context	= "$this->option.edit.$this->context";
		$recordId	= JRequest::getInt('id');

		if ($recordId) {
			// Check we are holding the id in the edit list.
			if (!$this->checkEditId($context, $recordId)) {
				// Somehow the person just went to the form - we don't allow that.
				$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $recordId));
				$this->setMessage($this->getError(), 'error');
				$this->setRedirect($this->getReturnPage());

				return false;
			}

			// If rows ids do not match, checkin previous row.
			if ($model->checkin($recordId) === false) {
				// Check-in failed, go back to the menu item and display a notice.
				$message = JText::sprintf('JERROR_CHECKIN_FAILED', $model->getError());
				$this->setRedirect('index.php?option=com_content&view=form&layout=edit&id='.$recordId, $message, 'error');
				return false;
			}
		}

		// Clear the menu item edit information from the session.
		$this->releaseEditId($context, $recordId);
		$app->setUserState($context.'.data',	null);

		// Redirect to the list screen.
		$this->setRedirect($this->getReturnPage());
	}

	/**
	 * Save the record
	 */
	public function save()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app		= JFactory::getApplication();
		$data		= JRequest::getVar('jform', array(), 'post', 'array');
		$model		= $this->getModel();
		$task		= $this->getTask();
		$context	= "$this->option.edit.$this->context";
		if (!in_array(JRequest::getWord('view'), array('category', 'categories'))) {
			$recordId = JRequest::getInt('id');
		} 
		else {
			$recordId = 0;
		}

		if (!$this->checkEditId($context, $recordId)) {
			// Somehow the person just went to the form and saved it - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $recordId));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect($this->getReturnPage());

			return false;
		}

		// Populate the row id from the session.
		$data['id'] = $recordId;

		// Split introtext and fulltext
		$pattern    = '#<hr\s+id=(["\'])system-readmore\1\s*/?>#i';
		$text		= $data['text'];
		$tagPos		= preg_match($pattern, $text);

		if ($tagPos == 0) {
			$data['introtext'] = $text;
		}
		else {
			list($data['introtext'], $data['fulltext']) = preg_split($pattern, $text, 2);
		}

		// The save2copy task needs to be handled slightly differently.
		if ($task == 'save2copy') {
			// Check-in the original row.
			if ($model->checkin() === false) {
				// Check-in failed, go back to the item and display a notice.
				$message = JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError());
				$this->setRedirect('index.php?option=com_content&view=form&layout=edit', $message, 'error');

				return false;
			}

			// Reset the ID and then treat the request as for Apply.
			$data['id']	= 0;
			$task		= 'apply';
		}

		// Validate the posted data.
		$form	= $model->getForm();
		if (!$form) {
			JError::raiseError(500, $model->getError());

			return false;
		}
		$data	= $model->validate($form, $data);

		// Check for validation errors.
		if ($data === false) {
			// Get the validation messages.
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if (JError::isError($errors[$i])) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Save the data in the session.
			$app->setUserState($context.'.data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_content&view=form&layout=edit', false));

			return false;
		}

		// Attempt to save the data.
		if (!$model->save($data)) {
			// Save the data in the session.
			$app->setUserState($context.'.data', $data);

			// Redirect back to the edit screen.
			$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_content&view=form&layout=edit', false));

			return false;
		}

		// Save succeeded, check-in the row.
		if ($model->checkin() === false) {
			// Check-in failed, go back to the row and display a notice.
			$message = JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_content&view=form&layout=edit', $message, 'error');

			return false;
		}

		if ($recordId == 0) {
			$this->setMessage(JText::_('COM_CONTENT_SUBMIT_SAVE_SUCCESS'));
		} 
		else {
			$this->setMessage(JText::_('COM_CONTENT_SAVE_SUCCESS'));
		}

		// Redirect the user and adjust session state based on the chosen task.
		switch ($task)
		{
			case 'apply':
				// Set the row data in the session.
				$recordId = $model->getState('article.id');
				$this->holdEditId($context, $recordId);
				$app->setUserState($context.'.data',	null);

				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_('index.php?option=com_content&view=form&layout=edit&id='.$recordId, false));
				break;

			case 'save2new':
				// Clear the row id and data in the session.
				$this->releaseEditId($context, $recordId);
				$app->setUserState($context.'.data',	null);

				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_('index.php?option=com_content&view=form&layout=edit', false));
				break;

			default:
				// Clear the row id and data in the session.
				$this->releaseEditId($context, $recordId);
				$app->setUserState($context.'.data',	null);

				// Redirect to the list screen.
				$this->setRedirect($this->getReturnPage());
				break;
		}
	}
}
