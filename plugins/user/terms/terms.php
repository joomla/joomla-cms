<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  User.terms
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

/**
 * An example custom terms and conditions plugin.
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgUserTerms extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 * @since  __DEPLOY_VERSION__
	 */
	protected $db;

	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An array that holds the plugin configuration
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		JFormHelper::addFieldPath(__DIR__ . '/field');
	}

	/**
	 * Adds additional fields to the user registration form
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onContentPrepareForm($form, $data)
	{
		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');

			return false;
		}

		// Check we are manipulating a valid form - we only display this on user registration form.
		$name = $form->getName();

		if (!in_array($name, array('com_users.registration')))
		{
			return true;
		}

		// Add the terms and conditions fields to the form.
		JForm::addFormPath(__DIR__ . '/terms');
		$form->loadFile('terms');

		$termsarticle = $this->params->get('terms_article');
		$termsnote    = $this->params->get('terms_note');

		// Push the terms and conditions article ID into the terms field.
		$form->setFieldAttribute('terms', 'article', $termsarticle, 'terms');
		$form->setFieldAttribute('terms', 'note', $termsnote, 'terms');
	}

	/**
	 * Method is called before user data is stored in the database
	 *
	 * @param   array    $user   Holds the old user data.
	 * @param   boolean  $isNew  True if a new user is stored.
	 * @param   array    $data   Holds the new user data.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  InvalidArgumentException on missing required data.
	 */
	public function onUserBeforeSave($user, $isNew, $data)
	{
		// // Only check for front-end user registration
		if ($this->app->isClient('administrator'))
		{
			return true;
		}

		$userId = ArrayHelper::getValue($user, 'id', 0, 'int');

		// User already registered, no need to check it further
		if ($userId > 0)
		{
			return true;
		}

		// Check that the terms is checked if required ie only in registration from frontend.
		$option = $this->app->input->getCmd('option');
		$task   = $this->app->input->get->getCmd('task');
		$form   = $this->app->input->post->get('jform', array(), 'array');

		if ($option == 'com_users' && in_array($task, array('registration.register')) && empty($form['terms']['terms']))
		{
			throw new InvalidArgumentException(Text::_('PLG_USER_TERMS_FIELD_ERROR'));
		}

		return true;

	}
}
