<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  User.terms
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Utilities\ArrayHelper;

/**
 * An example custom terms and conditions plugin.
 *
 * @since  3.9.0
 */
class PlgUserTerms extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.9.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  3.9.0
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    JDatabaseDriver
	 * @since  3.9.0
	 */
	protected $db;

	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An array that holds the plugin configuration
	 *
	 * @since   3.9.0
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		FormHelper::addFieldPath(__DIR__ . '/field');
	}

	/**
	 * Adds additional fields to the user registration form
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
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
		Form::addFormPath(__DIR__ . '/terms');
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
	 * @since   3.9.0
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

	/**
	 * Saves user profile data
	 *
	 * @param   array    $data    entered user data
	 * @param   boolean  $isNew   true if this is a new user
	 * @param   boolean  $result  true if saving the user worked
	 * @param   string   $error   error message
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 */
	public function onUserAfterSave($data, $isNew, $result, $error)
	{
		if (!$isNew || !$result)
		{
			return true;
		}

		JLoader::register('ActionlogsModelActionlog', JPATH_ADMINISTRATOR . '/components/com_actionlogs/models/actionlog.php');
		$userId = ArrayHelper::getValue($data, 'id', 0, 'int');

		$message = array(
			'action'      => 'consent',
			'id'          => $userId,
			'title'       => $data['name'],
			'itemlink'    => 'index.php?option=com_users&task=user.edit&id=' . $userId,
			'userid'      => $userId,
			'username'    => $data['username'],
			'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
		);

		/* @var ActionlogsModelActionlog $model */
		$model = BaseDatabaseModel::getInstance('Actionlog', 'ActionlogsModel');
		$model->addLog(array($message), 'PLG_USER_TERMS_LOGGING_CONSENT_TO_TERMS', 'plg_user_terms', $userId);
	}
}
