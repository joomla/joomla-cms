<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Remind confirmation model class.
 *
 * @since  3.9.0
 */
class PrivacyModelRemind extends JModelAdmin
{
	/**
	 * Confirms the remind request.
	 *
	 * @param   array  $data  The data expected for the form.
	 *
	 * @return  mixed  Exception | JException | boolean
	 *
	 * @since   3.9.0
	 */
	public function remindRequest($data)
	{
		// Get the form.
		$form = $this->getForm();
		$data['email'] = JStringPunycode::emailToPunycode($data['email']);

		// Check for an error.
		if ($form instanceof Exception)
		{
			return $form;
		}

		// Filter and validate the form data.
		$data = $form->filter($data);
		$return = $form->validate($data);

		// Check for an error.
		if ($return instanceof Exception)
		{
			return $return;
		}

		// Check the validation results.
		if ($return === false)
		{
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $formError)
			{
				$this->setError($formError->getMessage());
			}

			return false;
		}

		/** @var PrivacyTableConsent $table */
		$table = $this->getTable();

		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName(array('r.id', 'r.user_id', 'r.token')));
		$query->from($db->quoteName('#__privacy_consents', 'r'));
		$query->join('LEFT', $db->quoteName('#__users', 'u') . ' ON u.id = r.user_id');
		$query->where($db->quoteName('u.email') . ' = ' . $db->quote($data['email']));
		$query->where($db->quoteName('r.remind') . ' = 1');
		$db->setQuery($query);

		try
		{
			$remind = $db->loadObject();
		}
		catch (RuntimeException $e)
		{
			$this->setError(JText::_('COM_PRIVACY_ERROR_NO_PENDING_REMIND'));

			return false;
		}

		if (!$remind)
		{
			$this->setError(JText::_('COM_PRIVACY_ERROR_NO_PENDING_REMIND'));

			return false;
		}

		// Verify the token
		if (!JUserHelper::verifyPassword($data['remind_token'], $remind->token))
		{
			$this->setError(JText::_('COM_PRIVACY_ERROR_NO_REMIND_REQUESTS'));

			return false;
		}

		// Everything is good to go, transition the request to extended
		$saved = $this->save(
			array(
				'id'      => $remind->id,
				'remind'  => 0,
				'token'   => '',
				'created' => JFactory::getDate()->toSql(),
			)
		);

		if (!$saved)
		{
			// Error was set by the save method
			return false;
		}

		return true;
	}

	/**
	 * Method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm|boolean  A JForm object on success, false on failure
	 *
	 * @since   3.9.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_privacy.remind', 'remind', array('control' => 'jform'));

		if (empty($form))
		{
			return false;
		}

		$input = JFactory::getApplication()->input;

		if ($input->getMethod() === 'GET')
		{
			$form->setValue('remind_token', '', $input->get->getAlnum('remind_token'));
		}

		return $form;
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since   3.9.0
	 * @throws  \Exception
	 */
	public function getTable($name = 'Consent', $prefix = 'PrivacyTable', $options = array())
	{
		return parent::getTable($name, $prefix, $options);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	protected function populateState()
	{
		// Get the application object.
		$params = JFactory::getApplication()->getParams('com_privacy');

		// Load the parameters.
		$this->setState('params', $params);
	}
}
