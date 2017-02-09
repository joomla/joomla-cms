<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Fields controller class for Fields Component.
 *
 * @since  3.7.0
 */
class FieldsControllerField extends JControllerLegacy
{
	/**
	 * Stores the form content into the user session.
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	public function storeform()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app   = JFactory::getApplication();
		$data  = $this->input->get($this->input->get('formcontrol', 'jform'), array(), 'array');
		$parts = FieldsHelper::extract($this->input->getCmd('context'));

		if ($parts)
		{
			$app->setUserState($parts[0] . '.edit.' . $parts[1] . '.data', $data);
		}

		$app->redirect(base64_decode($this->input->get->getBase64('return')));
		$app->close();
	}
}
