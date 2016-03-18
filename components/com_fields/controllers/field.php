<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_fields
 * 
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class FieldsControllerField extends JControllerLegacy
{

	public function catchange ()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();
		$data = $this->input->get($this->input->get('formcontrol', 'jform'), array(), 'array');

		$parts = FieldsHelper::extract($this->input->getCmd('context'));
		if ($parts)
		{
			$app->setUserState($parts[0] . '.edit.' . $parts[1] . '.data', $data);
		}
		$app->redirect(base64_decode($this->input->get->getBase64('return')));
		$app->close();
	}
}
