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
 * Template style controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_templates
 * @since		1.6
 */
class TemplatesControllerStyle extends JControllerForm
{
	/**
	 * Override parent save method to deal with special template parameters.
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
		$paramsForm	= $model->getParamsForm($iData['template'], $iData['client_id']);
		if (!$paramsForm) {
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