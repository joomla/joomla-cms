<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
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
		if (empty($extensionId)) {
			$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_item.'&layout=edit', false));
			return JError::raiseWarning(500, 'COM_MODULES_ERROR_INVALID_EXTENSION');
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
	 * Override parent allowSave method.
	 */
	protected function allowSave(&$data, $key = 'id')
	{
		// use custom position if selected
		if (empty($data['position'])) {
			$data['position'] = $data['custom_position'];
		}

		unset($data['custom_position']);

		return parent::allowSave($data, $key);
	}

	/**
	 * Override parent save method.
	 */
	public function save()
	{
		if ($result = parent::save()) {
			$app = JFactory::getApplication();
			$app->setUserState('com_modules.add.module.extension_id', null);
		}
		return $result;
	}
}
