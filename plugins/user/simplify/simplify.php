<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  User.joomla
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Simplify User plugin
 *
 * @since  3.6
 */
class PlgUserSimplify extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 */
	protected $autoloadLanguage = true;

	/**
	 * Application object
	 *
	 * @var    JApplicationCms
	 */
	protected $app;

	/**
	 * Manipulate the user forms
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   3.6
	 */
	public function onContentPrepareForm($form, $data)
	{
		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');

			return false;
		}

		// Check we are manipulating a valid form.
		$name = $form->getName();

		if (!in_array($name, array('com_users.user', 'com_users.login', 'com_users.registration', 'com_users.profile')))
		{
			return true;
		}

		/*
		 * Check if we in right process step, we have a couple of options here to detect where we are. It is important
		 * to only manipulate the JForm object we use to display the form. For validation we need all fields because
		 * of the JTable checks, if name and username are filled and validation only works on fields that are part
		 * of the JForm object.
		 */

		if ($this->app->input->get('task') != '')
		{
			return true;
		}

		if ($name == 'com_users.profile')
		{
			// We resort the fields and change the label a bit so that it makes more sense
			$fields   = array('id', 'name', 'username', 'password1', 'password2', 'email1', 'email2');
			$newOrder = array('email1', 'email2', 'password1', 'password2');

			if ($this->params->get('remove_name_field', 1) == 0)
			{
				$newOrder = array_merge(array('name'), $newOrder);
			}

			$newOrder = array_merge(array('id'), $newOrder);

			$this->reorderFormFields($form, $fields, $newOrder);

			$form->setFieldAttribute('email1', 'label', 'PLG_USER_SIMPLIFY_EMAIL1');
			$form->setFieldAttribute('email2', 'label', 'PLG_USER_SIMPLIFY_EMAIL2');
		}

		if ($name == 'com_users.registration')
		{
			// We resort the fields and change the label a bit so that it makes more sense
			$fields   = array('spacer', 'name', 'username', 'password1', 'password2', 'email1', 'email2', 'captcha');
			$newOrder = array('email1', 'email2', 'password1', 'password2', 'captcha');

			if ($this->params->get('remove_name_field', 1) == 0)
			{
				$newOrder = array_merge(array('name'), $newOrder);
			}

			$newOrder = array_merge(array('spacer'), $newOrder);

			$this->reorderFormFields($form, $fields, $newOrder);

			$form->setFieldAttribute('email1', 'label', 'PLG_USER_SIMPLIFY_EMAIL1');
			$form->setFieldAttribute('email2', 'label', 'PLG_USER_SIMPLIFY_EMAIL2');
		}

		if ($name == 'com_users.user')
		{
			// We resort the fields and change the label a bit so that it makes more sense
			$fields   = array('name', 'username', 'password', 'password2', 'email', 'registerDate', 'lastvisitDate',
								'lastResetTime', 'resetCount', 'sendEmail', 'block', 'requireReset', 'id'
			);
			$newOrder = array('email', 'password', 'password2', 'registerDate', 'lastvisitDate', 'lastResetTime',
								'resetCount', 'sendEmail', 'block', 'requireReset', 'id'
			);

			if ($this->params->get('remove_name_field', 1) == 0)
			{
				$newOrder = array_merge(array('name'), $newOrder);
			}

			$this->reorderFormFields($form, $fields, $newOrder);

			$form->setFieldAttribute('email', 'label', 'PLG_USER_SIMPLIFY_EMAIL');
		}

		if ($name == 'com_users.login')
		{
			$form->setFieldAttribute('username', 'label', 'PLG_USER_SIMPLIFY_USERNAME');
		}

		return true;
	}

	/**
	 * Method is called before user data is validated
	 *
	 * @param   JForm  $form   The form to be altered.
	 * @param   mixed  &$data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   3.6
	 */
	public function onUserBeforeDataValidation($form, &$data)
	{
		if (array_key_exists('email', $data))
		{
			$value = $data['email'];
		}

		if (array_key_exists('email1', $data))
		{
			$value = $data['email1'];
		}

		if ($this->params->get('remove_name_field', 1) == 1)
		{
			$data['name'] = $value;
		}

		if ($this->params->get('remove_username_field', 1) == 1)
		{
			$data['username'] = $value;
		}

		return true;
	}

	/**
	 * Reorder fields of the form
	 *
	 * @param   JForm  $form      The form to be altered.
	 * @param   array  $fields    The fields of the form
	 * @param   array  $newOrder  The new ordering for the fields
	 *
	 * @return void
	 *
	 * @since   3.6
	 */
	private function reorderFormFields($form, $fields, $newOrder)
	{
		$formFields = array();

		foreach ($fields AS $field)
		{
			$formFields[$field] = $form->getFieldXml($field);

			$form->removeField($field);
		}

		foreach ($newOrder AS $field)
		{
			$form->setField($formFields[$field], null, false, 'fieldset');
		}
	}
}
