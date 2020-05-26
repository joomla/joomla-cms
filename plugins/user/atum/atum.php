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
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * Plugin for managing Atum template settings per user.
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgUserAtum extends CMSPlugin
{
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
		if (!in_array($form->getName(), ['com_admin.profile', 'com_users.user'], true))
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
}
