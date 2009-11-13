<?php
/**
 * @version		$Id: controller.php 12685 2009-09-10 14:14:04Z pentacle $
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Plugin controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_plugins
 * @since		1.6
 */
class PluginsControllerPlugin extends JControllerForm
{
	/**
	 * Override parent save method to deal with special plugin parameters.
	 *
	 * @return	void
	 */
	public function save()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialise variables.
		$iData	= JRequest::getVar('jform', array(), 'post', 'array');
		$pData	= JRequest::getVar('jformparams', array(), 'post', 'array');
		$model	= $this->getModel();

		// Get the template parameter form.
		$paramsForm	= $model->getParamsForm($iData['folder'], $iData['element']);
		if (!$paramsForm)
		{
			JError::raiseError(500, $model->getError());
			return false;
		}

		// Validate and inject back into the main form data.
		$pData	= $model->validate($paramsForm, $pData);
		$iData['params'] = $pData;

		JRequest::setVar('jform', $iData, 'post');

		return parent::save();
	}
}