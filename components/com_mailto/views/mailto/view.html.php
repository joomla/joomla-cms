<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_mailto
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Class for Mail.
 * 
 * @since  1.5
 */
class MailtoViewMailto extends JViewLegacy
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since   1.5
	 */
	public function display($tpl = null)
	{
		$data = $this->getData();

		if ($data === false)
		{
			return false;
		}

		$this->set('data', $data);

		return parent::display($tpl);
	}

	/**
	 * Get the form data
	 *
	 * @return  object
	 *
	 * @since  1.5
	 */
	protected function &getData()
	{
		$user = JFactory::getUser();
		$app  = JFactory::getApplication();
		$data = new stdClass;

		$input      = $app->input;
		$method     = $input->getMethod();
		$data->link = urldecode($input->$method->get('link', '', 'BASE64'));

		if ($data->link == '')
		{
			JError::raiseError(403, JText::_('COM_MAILTO_LINK_IS_MISSING'));

			return false;
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
