<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * @package		Joomla.Site
 * @subpackage	com_content
 */
class ContentControllerArticle extends JController
{
	protected $_context = 'com_content.edit.article';

	/**
	 * Constructor
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
	 * @return	object	The model.
	 * @since	1.5
	 */
	public function &getModel($name = 'form', $prefix = '', $config = array())
	{
		return parent::getModel($name, $prefix, $config);
	}

	protected function _getReturnPage()
	{
		$app 		= &JFactory::getApplication();
		$context	= $this->_context.'.';

		if (!($return = $app->getUserState($context.'.return'))) {
			$return = JRequest::getVar('return', base64_encode(JURI::base()));
		}

		$return = JFilterInput::clean($return, 'base64');
		$return = base64_decode($return);

		if (!JURI::isInternal($return)) {
			$return = JURI::base();
		}

		return $return;
	}

	protected function _setReturnPage()
	{
		$app 		= &JFactory::getApplication();
		$context	= $this->_context.'.';

		$return = JRequest::getVar('return', null, 'default', 'base64');

		$app->setUserState($context.'return', $return);
	}

	/**
	 * Method to add a new record.
	 *
	 * @return	void
	 */
	public function add()
	{
		$app		= &JFactory::getApplication();
		$context	= $this->_context.'.';

		// Access check
		if (!JFactory::getUser()->authorise('core.create', 'com_content')) {
			JError::raiseError(403, JText::_('ALERTNOTAUTH'));
			return false;
		}

		// Clear the record edit information from the session.
		$app->setUserState($context.'id', null);
		$app->setUserState($context.'data', null);
		$this->_setReturnPage();

		// Redirect to the edit screen.
		$this->setRedirect(JRoute::_('index.php?option=com_content&view=form&layout=edit', false));
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
		$app		= &JFactory::getApplication();
		$context	= $this->_context.'.';
		$ids		= JRequest::getVar('cid', array(), '', 'array');

		// Get the id of the group to edit.
		$id =  (int) (empty($ids) ? JRequest::getInt('id') : array_pop($ids));

		// Access check
		if (!JFactory::getUser()->authorise('core.edit', 'com_content.article.'.$id)) {
			JError::raiseError(403, JText::_('ALERTNOTAUTH'));
			return false;
		}

		// Get the previous row id (if any) and the current row id.
		$previousId	= (int) $app->getUserState($context.'id');
		$app->setUserState($context.'id', $id);
		$this->_setReturnPage();

		// Get the menu item model.
		$model = &$this->getModel();

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
					$this->setRedirect('index.php?option=com_content&view=article&item_id='.$id, $message, 'error');
					return false;
				}
			}
		}

		// Check-out succeeded, push the new row id into the session.
		$app->setUserState($context.'id',	$id);
		$app->setUserState($context.'data',	null);

		$this->setRedirect('index.php?option=com_content&view=form&layout=edit');

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
		$app 		= &JFactory::getApplication();
		$context 	= $this->_context.'.';

		// Get the previous menu item id (if any) and the current menu item id.
		$previousId	= (int) $app->getUserState($context.'id');

		// Get the menu item model.
		$model = &$this->getModel();

		// If rows ids do not match, checkin previous row.
		if (!$model->checkin($previousId))
		{
			// Check-in failed, go back to the menu item and display a notice.
			$message = JText::sprintf('JError_Checkin_failed', $model->getError());
			$this->setRedirect('index.php?option=com_content&view=form&layout=edit', $message, 'error');
			return false;
		}

		// Clear the menu item edit information from the session.
		$app->setUserState($context.'id',	null);
		$app->setUserState($context.'data',	null);

		// Redirect to the list screen.
		$this->setRedirect($this->_getReturnPage());
	}


	/**
	 * Save the record
	 */
	public function save()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialise variables.
		$app		= &JFactory::getApplication();
		$context 	= $this->_context.'.';
		$model		= &$this->getModel();
		$task		= $this->getTask();

		// Get posted form variables.
		$data		= JRequest::getVar('jform', array(), 'post', 'array');

		// Populate the row id from the session.
		$data['id'] = (int) $app->getUserState($context.'id');

		// Split introtext and fulltext
		$pattern 	= '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
		$text 		= $data['text'];
		$tagPos		= preg_match($pattern, $text);

		if ($tagPos == 0) {
			$data['introtext'] = $text;
		}
		else {
			list($data['introtext'], $data['fulltext']) = preg_split($pattern, $text, 2);
		}

		// The save2copy task needs to be handled slightly differently.
		if ($task == 'save2copy')
		{
			// Check-in the original row.
			if (!$model->checkin())
			{
				// Check-in failed, go back to the item and display a notice.
				$message = JText::sprintf('JError_Checkin_saved', $model->getError());
				$this->setRedirect('index.php?option=com_content&view=form&layout=edit', $message, 'error');
				return false;
			}

			// Reset the ID and then treat the request as for Apply.
			$data['id']	= 0;
			$task		= 'apply';
		}

		// Validate the posted data.
		$form	= &$model->getForm();
		if (!$form) {
			JError::raiseError(500, $model->getError());
			return false;
		}
		$data	= $model->validate($form, $data);

		// Check for validation errors.
		if ($data === false)
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
			$app->setUserState($context.'data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_content&view=form&layout=edit', false));
			return false;
		}

		// Attempt to save the data.
		if (!$model->save($data))
		{
			// Save the data in the session.
			$app->setUserState($context.'data', $data);

			// Redirect back to the edit screen.
			$this->setMessage(JText::sprintf('JError_Save_failed', $model->getError()), 'notice');
			$this->setRedirect(JRoute::_('index.php?option=com_content&view=form&layout=edit', false));
			return false;
		}

		// Save succeeded, check-in the row.
		if (!$model->checkin())
		{
			// Check-in failed, go back to the row and display a notice.
			$message = JText::sprintf('JError_Checkin_saved', $model->getError());
			$this->setRedirect('index.php?option=com_content&view=form&layout=edit', $message, 'error');
			return false;
		}

		$this->setMessage(JText::_('JController_Save_success'));

		// Redirect the user and adjust session state based on the chosen task.
		switch ($task)
		{
			case 'apply':
				// Set the row data in the session.
				$app->setUserState($context.'id',	$model->getState('article.id'));
				$app->setUserState($context.'data',	null);

				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_('index.php?option=com_content&view=form&layout=edit', false));
				break;

			case 'save2new':
				// Clear the row id and data in the session.
				$app->setUserState($context.'id',	null);
				$app->setUserState($context.'data',	null);

				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_('index.php?option=com_content&view=form&layout=edit', false));
				break;

			default:
				// Clear the row id and data in the session.
				$app->setUserState($context.'id',	null);
				$app->setUserState($context.'data',	null);

				// Redirect to the list screen.
				$this->setRedirect($this->_getReturnPage());
				break;
		}
	}
}