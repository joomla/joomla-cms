<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_mailto
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class MailtoViewMailto extends JViewLegacy
{
	function display($tpl = null)
	{
		$data = $this->getData();
		if ($data === false) {
			return false;
		}

		$this->set('data'  , $data);

		parent::display($tpl);
	}

	function &getData()
	{
		$user = JFactory::getUser();
		$data = new stdClass();

		$data->link = urldecode(JRequest::getVar('link', '', 'method', 'base64'));

		if ($data->link == '') {
			JError::raiseError(403, JText::_('COM_MAILTO_LINK_IS_MISSING'));
			$false = false;
			return $false;
		}

		// Load with previous data, if it exists
		$mailto		= JRequest::getString('mailto', '', 'post');
		$sender		= JRequest::getString('sender', '', 'post');
		$from		= JRequest::getString('from', '', 'post');
		$subject	= JRequest::getString('subject', '', 'post');

		if ($user->get('id') > 0) {
			$data->sender	= $user->get('name');
			$data->from		= $user->get('email');
		}
		else
		{
			$data->sender	= $sender;
			$data->from		= $from;
		}

		$data->subject	= $subject;
		$data->mailto	= $mailto;

		return $data;
	}
}
