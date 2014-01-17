<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_mailto
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * @package     Joomla.Site
 * @subpackage  com_mailto
 * @since       1.5
 */
class MailtoViewMailto extends JViewLegacy
{
	/**
	 * @since  1.5
	 */
	public function display($tpl = null)
	{
		$data = $this->getData();
		if ($data === false)
		{
			return false;
		}

		$this->set('data', $data);

		parent::display($tpl);
	}

	/**
	 * @since  1.5
	 */
	function &getData()
	{
		$user = JFactory::getUser();
		$app  = JFactory::getApplication();
		$data = new stdClass;

		$data->link = urldecode(JRequest::getVar('link', '', 'method', 'base64'));

		if ($data->link == '')
		{
			JError::raiseError(403, JText::_('COM_MAILTO_LINK_IS_MISSING'));
			$false = false;
			return $false;
		}

		// Load with previous data, if it exists
		$mailto  = $app->input->post->getString('mailto', '');
		$sender  = $app->input->post->getString('sender', '');
		$from    = $app->input->post->getString('from', '');
		$subject = $app->input->post->getString('subject', '');

		if ($user->get('id') > 0)
		{
			$data->sender = $user->get('name');
			$data->from   = $user->get('email');
		}
		else
		{
			$data->sender = $sender;
			$data->from   = JStringPunycode::emailToPunycode($from);
		}

		$data->subject = $subject;
		$data->mailto  = JStringPunycode::emailToPunycode($mailto);

		return $data;
	}
}
