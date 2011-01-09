<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
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
	protected $view_item = 'form';

	/**
	 * @since	1.6
	 */
	protected $view_list = 'categories';

	/**
	 * Method override to check if you can add a new record.
	 *
	 * @param	array	$data	An array of input data.
	 * @return	boolean
	 * @since	1.6
	 */
	protected function allowAdd($data = array())
	{
		// Initialise variables.
		$user		= JFactory::getUser();
		$categoryId	= JArrayHelper::getValue($data, 'catid', JRequest::getInt('id'), 'int');
		$allow		= null;

		if ($categoryId) {
			// If the category has been passed in the URL check it.
			$allow	= $user->authorise('core.create', $this->option.'.category.'.$categoryId);
		}

		if ($allow === null) {
			// In the absense of better information, revert to the component permissions.
			return parent::allowAdd($data);
		} else {
			return $allow;
		}
	}

	/**
	 * Method to check if you can add a new record.
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
		$categoryId = 0;

		if ($recordId) {
			$categoryId = (int) $this->getModel()->getItem($recordId)->catid;
		}

		if ($categoryId) {
			// The category has been set. Check the category permissions.
			return JFactory::getUser()->authorise('core.edit', $this->option.'.category.'.$categoryId);
		} else {
			// Since there is no asset tracking, revert to the component permissions.
			return parent::allowEdit($data, $key);
		}
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @param	string	$key	The name of the primary key of the URL variable.
	 *
	 * @return	Boolean	True if access level checks pass, false otherwise.
	 * @since	1.6
	 */
	public function cancel($key = 'w_id')
	{
		parent::cancel($key);

		// Redirect to the list screen.
		$this->setRedirect($this->_getReturnPage());
	}

	/**
	 * Method to edit an existing record.
	 *
	 * @param	string	$key	The name of the primary key of the URL variable.
	 * @param	string	$urlVar	The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return	Boolean	True if access level check and checkout passes, false otherwise.
	 * @since	1.6
	 */
	public function edit($key = null, $urlVar = 'w_id')
	{
		$result = parent::edit($key, $urlVar);
		$this->_setReturnPage();

		return $result;
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
	public function &getModel($name = 'form', $prefix = '', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param	int		$recordId	The primary key id for the item.
	 * @param	string	$urlVar		The name of the URL variable for the id.
	 *
	 * @return	string	The arguments to append to the redirect URL.
	 * @since	1.6
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = null)
	{
		$append = parent::getRedirectToItemAppend($recordId, $urlVar);
		$itemId	= JRequest::getInt('Itemid');

		if ($itemId) {
			$append .= '&Itemid='.$itemId;
		}

		return $append;
	}

	/**
	 * Function that allows child controller access to model data after the data has been saved.
	 *
	 * @param	JModel	$model		The data model object.
	 * @param	array	$validData	The validated data.
	 *
	 * @return	void
	 * @since	1.6
	 */
	protected function postSaveHook(JModel &$model, $validData)
	{
		$task = $this->getTask();

		if ($task == 'save') {
			$this->setRedirect(JRoute::_('index.php?option=com_weblinks&view=category&id='.$validData['catid'], false));
		}
	}

	/**
	 * Method to save a record.
	 *
	 * @param	string	$key	The name of the primary key of the URL variable.
	 * @param	string	$urlVar	The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return	Boolean	True if successful, false otherwise.
	 * @since	1.6
	 */
	public function save($key = null, $urlVar = 'w_id')
	{
		$result = parent::save($key, $urlVar);

		return $result;
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

	protected function _getReturnPage()
	{
		$app		= JFactory::getApplication();

		if (!($return = $app->getUserState($this->context.'return'))) {
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

		$return = JRequest::getVar('return', null, 'default', 'base64');

		$app->setUserState($this->context.'return', $return);
	}
}