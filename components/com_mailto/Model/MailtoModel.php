<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_mailto
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mailto\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\String\PunycodeHelper;

/**
 * Mailto model class.
 *
 * @since  3.8.9
 */
class MailtoModel extends FormModel
{
	/**
	 * Method to get the mailto form.
	 *
	 * The base form is loaded from XML and then an event is fired
	 * for users plugins to extend the form with extra fields.
	 *
	 * @param   array    $data      An optional array of data for the form to interrogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form|boolean  A JForm object on success, false on failure
	 *
	 * @since   3.8.9
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
	 * @since   3.8.9
	 */
	protected function loadFormData()
	{
		$user = Factory::getUser();
		$app  = Factory::getApplication();
		$data = $app->getUserState('mailto.mailto.form.data', array());

		$data['link'] = urldecode($app->input->get('link', '', 'BASE64'));

		if ($data['link'] == '')
		{
			throw new \RuntimeException(Text::_('COM_MAILTO_LINK_IS_MISSING'));
		}

		// Load with previous data, if it exists
		$data['sender']    = $app->input->post->getString('sender', '');
		$data['subject']   = $app->input->post->getString('subject', '');
		$data['emailfrom'] = PunycodeHelper::emailToPunycode($app->input->post->getString('emailfrom', ''));
		$data['emailto']   = PunycodeHelper::emailToPunycode($app->input->post->getString('emailto', ''));

		if (!$user->guest)
		{
			$data['sender']    = $user->name;
			$data['emailfrom'] = $user->email;
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
	 * @since   3.8.9
	 */
	public function getData()
	{
		$input = Factory::getApplication()->input;

		$data['emailto']    = $input->get('emailto', '', 'string');
		$data['sender']     = $input->get('sender', '', 'string');
		$data['emailfrom']  = $input->get('emailfrom', '', 'string');
		$data['subject']    = $input->get('subject', '', 'string');
		$data['consentbox'] = $input->get('consentbox', '', 'string');

		return $data;
	}
}
