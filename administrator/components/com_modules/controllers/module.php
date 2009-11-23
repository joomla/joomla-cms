<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Module controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_modules
 * @version		1.6
 */
class ModulesControllerModule extends JControllerForm
{
	/**
	 * Override parent add method.
	 */
	public function add()
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Get the result of the parent method. If an error, just return it.
		$result = parent::add();
		if (JError::isError($result)) {
			return $result;
		}

		// Look for the Extension ID.
		$extensionId = JRequest::getInt('eid');
		if (empty($extensionId))
		{
			$this->setRedirect(JRoute::_('index.php?option='.$this->_option.'&view='.$this->_view_item.'&layout=edit', false));
			return JError::raiseWarning(500, 'Modules_Error_invalid_extension');
		}

		$app->setUserState('com_modules.add.module.extension_id', $extensionId);
	}

	/**
	 * Override parent cancel method to reset the add module state.
	 */
	public function cancel()
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		parent::cancel();

		$app->setUserState('com_modules.add.module.extension_id', null);
	}

	/**
	 * Override parent save method to deal with raw data.
	 */
	public function save()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialise variables.
		$app	= JFactory::getApplication();
		$iData	= JRequest::getVar('jform', array(), 'post', 'array');
		$pData	= JRequest::getVar('jformparams', array(), 'post', 'array');
		$model	= $this->getModel();

		// Get the template parameter form.
		$paramsForm	= $model->getParamsForm($iData['module'], $iData['client_id']);
		if (!$paramsForm)
		{
			JError::raiseError(500, $model->getError());
			return false;
		}

		// Validate and inject back into the main form data.
		$pData	= $model->validate($paramsForm, $pData);
		$iData['params'] = $pData;

		JRequest::setVar('jform', $iData, 'post');

		if ($result = parent::save())
		{
			$app->setUserState('com_modules.add.module.extension_id', null);
		}

		return result;
	}
}