<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Mailto model class.
 *
 * @since  __DEPLOY_VERSION__
 */
class MailtoModelMailto extends JModelForm
{
	/**
	 * Method to get the mailto form.
	 *
	 * The base form is loaded from XML and then an event is fired
	 * for users plugins to extend the form with extra fields.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm	A JForm object on success, false on failure
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_mailto.mailto', 'mailto', array('load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array  The default data is an empty array.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function loadFormData()
	{
		$user = JFactory::getUser();
		$app  = JFactory::getApplication();
		$data = array();

		$data = $app->getUserState('mailto.mailto.form.data', array());

		$data['link'] = urldecode($app->input->get('link', '', 'BASE64'));

		if ($data['link'] == '')
		{
			JError::raiseError(403, JText::_('COM_MAILTO_LINK_IS_MISSING'));

			return false;
		}

		// Load with previous data, if it exists
		$data['sender']  = $app->input->post->getString('sender', '');
		$data['subject'] = $app->input->post->getString('subject', '');
		$data['from']    = JStringPunycode::emailToPunycode($app->input->post->getString('from', ''));
		$data['mailto']  = JStringPunycode::emailToPunycode($app->input->post->getString('mailto', ''));

		if (!$user->guest)
		{
			$data['sender'] = $user->name;
			$data['from']   = $user->email;
		}

		$app->setUserState('mailto.mailto.form.data', $data);

		$this->preprocessData('com_mailto.mailto', $data);

		return $data;
	}

	/**
	 * Get the request data
	 *
	 * @return  array  The requested data
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getData()
	{
		$input = JFactory::getApplication()->input;

		$data['emailto']   = $input->get('emailto', '', 'string');
		$data['sender']    = $input->get('sender', '', 'string');
		$data['emailfrom'] = $input->get('emailfrom', '', 'string');
		$data['subject']   = $input->get('subject', '', 'string');
		$data['captcha']   = $input->get('captcha', '', 'string');

		return $data;
	}
}
