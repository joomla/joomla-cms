<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  User.Atum
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * Plugin for managing Atum template settings per user.
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgUserAtum extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    \Joomla\CMS\Application\CMSApplication
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Adds additional fields to the user editing form
	 *
	 * @param   Form   $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onContentPrepareForm(Form $form, $data)
	{
		// Check we are manipulating the correct form.
		if (!in_array($form->getName(), ['com_admin.profile', 'com_users.user', 'com_users.profile'], true))
		{
			return true;
		}

		// Check if user can login to backend.
		if (!$this->app->getIdentity()->authorise('core.login.admin'))
		{
			return true;
		}

		// Load language files.
		$this->loadLanguage();

		// Add Atum fields to the form.
		FormHelper::addFormPath(__DIR__ . '/forms');
		$form->loadFile('params');

		return true;
	}

	/**
	 * Registers methods for rendering form value.
	 *
	 * @param   string  $context  The context for the data
	 * @param   object  $data     An object containing the data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onContentPrepareData($context, $data)
	{
		if (!in_array($context, ['com_admin.profile', 'com_users.user', 'com_users.profile'], true))
		{
			return true;
		}

		// Load language files.
		$this->loadLanguage();

		$fields = ['monochrome', 'contrast', 'highlight', 'fontsize'];

		foreach ($fields as $field)
		{
			if (!HTMLHelper::isRegistered('users.jform_params_atum_' . $field))
			{
				HTMLHelper::register('users.jform_params_atum_' . $field, [__CLASS__, 'renderValue']);
			}
		}

		return true;
	}

	/**
	 * Renders a value.
	 *
	 * @param   integer|string  $value  The value (0 or 1 or empty string if not set).
	 *
	 * @return  string  The rendered value.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function renderValue($value)
	{
		if ($value === '')
		{
			return Text::_('PLG_USER_ATUM_DEFAULT_VALUE');
		}

		return Text::_($value ? 'JYES' : 'JNO');
	}
}
