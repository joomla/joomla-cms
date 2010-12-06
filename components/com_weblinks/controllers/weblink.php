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
 * @subpackage	com_weblinks
 * @since		1.5
 */
class WeblinksControllerWeblink extends JControllerForm
{
	/**
	 * @since	1.6
	 */
	protected $context = 'com_weblinks.edit.weblink';

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
	 * Method to get a model object, loading it if required.
	 *
	 * @param	string	The model name. Optional.
	 * @param	string	The class prefix. Optional.
	 * @param	array	Configuration array for model. Optional.
	 *
	 * @return	object	The model.
	 * @since	1.5
	 */
	public function &getModel($name = 'form', $prefix = '', $config = array())
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}
	protected function _getReturnPage()
	{
		$app		= JFactory::getApplication();
		$context	= $this->context.'.';

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

	protected function _setReturnPage()
	{
		$app		= JFactory::getApplication();
		$context	= $this->context.'.';

		$return = JRequest::getVar('return', null, 'default', 'base64');

		$app->setUserState($context.'return', $return);
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
		$context	= $this->context.'.';
		$model		= $this->getModel();
		$task		= $this->getTask();

		// Get posted form variables.
		$data		= JRequest::getVar('jform', array(), 'post', 'array');
		$catid		= $data['catid'];
		// Populate the row id from the session.
		$data['id'] = (int) $app->getUserState($context.'id');		
		
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
					$app->enqueueMessage($errors[$i]->getMessage(), 'notice');
				}
				else {
					$app->enqueueMessage($errors[$i], 'notice');
				}
			}

		}
			// Attempt to save the data.
		if (!$model->save($data)) {
			// Save the data in the session.
			$app->setUserState($context.'data', $data);

			// Redirect back to the edit screen.
			$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'notice');
			$this->setRedirect(JRoute::_('index.php?option=com_weblinks&view=form&layout=edit', false));
			return false;
		}

		// Save succeeded, check-in the row.
		if ($model->checkin() === false) {
			// Check-in failed, go back to the row and display a notice.
			$message = JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_weblinks&view=form&layout=edit', $message, 'error');
			return false;
		}

		if ($data['id'] == 0) {
			$this->setMessage(JText::_('COM_WEBLINKS_SUBMIT_SAVE_SUCCESS'));
		} 
		else {
			$this->setMessage(JText::_('COM_WEBLINKS_SAVE_SUCCESS'));
		}

		// Redirect the user and adjust session state based on the chosen task.
		switch ($task)
		{
			case 'apply':
				// Set the row data in the session.
				$app->setUserState($context.'id',	$model->getState('weblink.id'));
				$app->setUserState($context.'data',	null);

				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_('index.php?option=com_weblinks&view=form&layout=edit', false));
				break;

			case 'save2new':
				// Clear the row id and data in the session.
				$app->setUserState($context.'id',	null);
				$app->setUserState($context.'data',	null);

				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_('index.php?option=com_weblinks&task=edit.weblink', false));
				break;

			default:
				// Clear the row id and data in the session.
				$app->setUserState($context.'id',	null);
				$app->setUserState($context.'data',	null);
				$this->setRedirect( JRoute::_( 'index.php?option=com_weblinks&view=category&id='.$catid, false ) );
		}
	}

	/**
	 * Method to edit a object
	 *
	 * Sets object ID in the session from the request, checks the item out, and then redirects to the edit page.
	 *
	 * @access	public
	 * @return	void
	 */
	public function edit()
	{
		// Initialise variables.
		$app		= JFactory::getApplication();
		$context	= $this->context.'.';
		$ids		= JRequest::getVar('cid', array(), '', 'array');

		// Get the id of the group to edit.
		$id =  (int) (empty($ids) ? JRequest::getInt('id') : array_pop($ids));

		// Access check
		if (!JFactory::getUser()->authorise('core.edit', 'com_weblinks.weblink.'.$id)) {
			JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
			return false;
		}

		// Get the previous row id (if any) and the current row id.
		$previousId	= (int) $app->getUserState($context.'id');
		$app->setUserState($context.'id', $id);
		$this->_setReturnPage();

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
					$this->setRedirect('index.php?option=com_weblinks&view=categories', $message, 'error');
					return false;
				}
			}
		}

		// Check-out succeeded, push the new row id into the session.
		$app->setUserState($context.'id',	$id);
		$app->setUserState($context.'data',	null);

		// ItemID required on redirect for correct Template Style
		$redirect = 'index.php?option=com_weblinks&view=form&layout=edit&id='.$id;
		if (JRequest::getInt('Itemid') == 0) {
		}
		else {
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
	 * @access	public
	 * @return	void
	 */
	public function cancel()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialise variables.
		$app		= JFactory::getApplication();
		$context	= $this->context.'.';

		// Redirect to the list screen.
		$this->setRedirect($this->_getReturnPage());
	}


	/**
	 * Go to a weblink
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function go()
	{
		// Get the ID from the request
		$id = JRequest::getInt('id');

		// Get the model, requiring published items
		$modelLink	= $this->getModel('Weblink', '', array('ignore_request' => true));
		$modelLink->setState('filter.published', 1);

		// Get the item
		$link	= $modelLink->getItem($id);

		// Make sure the item was found.
		if (empty($link)) {
			return JError::raiseWarning(404, JText::_('COM_WEBLINKS_ERROR_WEBLINK_NOT_FOUND'));
		}

		// Check whether item access level allows access.
		$user	= JFactory::getUser();
		$groups	= $user->getAuthorisedViewLevels();

		if (!in_array($link->access, $groups)) {
			return JError::raiseError(403, JText::_("JERROR_ALERTNOAUTHOR"));
		}

		// Check whether category access level allows access.
		$modelCat = $this->getModel('Category', 'WeblinksModel', array('ignore_request' => true));
		$modelCat->setState('filter.published', 1);

		// Get the category
		$category = $modelCat->getCategory($link->catid);

		// Make sure the category was found.
		if (empty($category)) {
			return JError::raiseWarning(404, JText::_('COM_WEBLINKS_ERROR_WEBLINK_NOT_FOUND'));
		}

		// Check whether item access level allows access.
		if (!in_array($category->access, $groups)) {
			return JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// Redirect to the URL
		// TODO: Probably should check for a valid http link
		if ($link->url) {
			$modelLink->hit($id);
			JFactory::getApplication()->redirect($link->url);
		}
		else {
			return JError::raiseWarning(404, JText::_('COM_WEBLINKS_ERROR_WEBLINK_URL_INVALID'));
		}
	}
}
