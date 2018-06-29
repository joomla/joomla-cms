<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Users\Site\Controller;

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Help\Help;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Client\ClientHelper;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

/**
 * Profile controller class for Users.
 *
 * @since  1.6
 */
class ProfileController extends BaseController
{
	/**
	 * Method to check out a user for editing and redirect to the edit form.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function edit()
	{
		$app         = $this->app;
		$user        = Factory::getUser();
		$loginUserId = (int) $user->get('id');

		// Get the previous user id (if any) and the current user id.
		$previousId = (int) $app->getUserState('com_users.edit.profile.id');
		$userId     = $this->input->getInt('user_id');

		// Check if the user is trying to edit another users profile.
		if ($userId != $loginUserId)
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->setHeader('status', 403, true);

			return false;
		}

		$cookieLogin = $user->get('cookieLogin');

		// Check if the user logged in with a cookie
		if (!empty($cookieLogin))
		{
			// If so, the user must login to edit the password and other data.
			$app->enqueueMessage(Text::_('JGLOBAL_REMEMBER_MUST_LOGIN'), 'message');
			$this->setRedirect(Route::_('index.php?option=com_users&view=login', false));

			return false;
		}

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_users.edit.profile.id', $userId);

		// Get the model.
		/* @var \Joomla\Component\Users\Site\Model\ProfileModel $model */
		$model = $this->getModel('Profile', 'Site');

		// Check out the user.
		if ($userId)
		{
			$model->checkout($userId);
		}

		// Check in the previous user.
		if ($previousId)
		{
			$model->checkin($previousId);
		}

		// Redirect to the edit screen.
		$this->setRedirect(Route::_('index.php?option=com_users&view=profile&layout=edit', false));

		return true;
	}

	/**
	 * Method to save a user's profile data.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 * @throws  \Exception
	 */
	public function save()
	{
		// Check for request forgeries.
		$this->checkToken();

		$app    = $this->app;

		/* @var \Joomla\Component\Users\Site\Model\ProfileModel $model */
		$model  = $this->getModel('Profile', 'Site');
		$user   = Factory::getUser();
		$userId = (int) $user->get('id');

		// Get the user data.
		$requestData = $app->input->post->get('jform', array(), 'array');

		// Force the ID to this user.
		$requestData['id'] = $userId;

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			throw new \Exception($model->getError(), 500);
		}

		// Validate the posted data.
		$data = $model->validate($form, $requestData);

		// Check for errors.
		if ($data === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof \Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Unset the passwords.
			unset($requestData['password1'], $requestData['password2']);

			// Save the data in the session.
			$app->setUserState('com_users.edit.profile.data', $requestData);

			// Redirect back to the edit screen.
			$userId = (int) $app->getUserState('com_users.edit.profile.id');
			$this->setRedirect(Route::_('index.php?option=com_users&view=profile&layout=edit&user_id=' . $userId, false));

			return false;
		}

		// Attempt to save the data.
		$return = $model->save($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_users.edit.profile.data', $data);

			// Redirect back to the edit screen.
			$userId = (int) $app->getUserState('com_users.edit.profile.id');
			$this->setMessage(Text::sprintf('COM_USERS_PROFILE_SAVE_FAILED', $model->getError()), 'warning');
			$this->setRedirect(Route::_('index.php?option=com_users&view=profile&layout=edit&user_id=' . $userId, false));

			return false;
		}

		// Redirect the user and adjust session state based on the chosen task.
		switch ($this->getTask())
		{
			case 'apply':
				// Check out the profile.
				$app->setUserState('com_users.edit.profile.id', $return);
				$model->checkout($return);

				// Redirect back to the edit screen.
				$this->setMessage(Text::_('COM_USERS_PROFILE_SAVE_SUCCESS'));

				$redirect = $app->getUserState('com_users.edit.profile.redirect');

				// Don't redirect to an external URL.
				if (!Uri::isInternal($redirect))
				{
					$redirect = null;
				}

				if (!$redirect)
				{
					$redirect = 'index.php?option=com_users&view=profile&layout=edit&hidemainmenu=1';
				}

				$this->setRedirect(Route::_($redirect, false));
				break;

			default:
				// Check in the profile.
				$userId = (int) $app->getUserState('com_users.edit.profile.id');

				if ($userId)
				{
					$model->checkin($userId);
				}

				// Clear the profile id from the session.
				$app->setUserState('com_users.edit.profile.id', null);

				$redirect = $app->getUserState('com_users.edit.profile.redirect');

				// Don't redirect to an external URL.
				if (!Uri::isInternal($redirect))
				{
					$redirect = null;
				}

				if (!$redirect)
				{
					$redirect = 'index.php?option=com_users&view=profile&user_id=' . $return;
				}

				// Redirect to the list screen.
				$this->setMessage(Text::_('COM_USERS_PROFILE_SAVE_SUCCESS'));
				$this->setRedirect(Route::_($redirect, false));
				break;
		}

		// Flush the data from the session.
		$app->setUserState('com_users.edit.profile.data', null);
	}

	/**
	 * Function that allows child controller access to model data after the data has been saved.
	 *
	 * @param   BaseDatabaseModel  $model      The data model object.
	 * @param   array              $validData  The validated data.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function postSaveHook(BaseDatabaseModel $model, $validData = array())
	{
		$item = $model->getData();
		$tags = $validData['tags'];

		if ($tags)
		{
			$item->tags = new TagsHelper;
			$item->tags->getTagIds($item->id, 'com_users.user');
			$item->metadata['tags'] = $item->tags;
		}
	}

	/**
	 * Returns the updated options for help site selector
	 *
	 * @return  void
	 *
	 * @since   3.5
	 * @throws  \Exception
	 */
	public function gethelpsites()
	{
		jimport('joomla.filesystem.file');

		// Set FTP credentials, if given
		ClientHelper::setCredentialsFromRequest('ftp');

		if (($data = file_get_contents('https://update.joomla.org/helpsites/helpsites.xml')) === false)
		{
			throw new \Exception(Text::_('COM_CONFIG_ERROR_HELPREFRESH_FETCH'), 500);
		}
		elseif (!File::write(JPATH_ADMINISTRATOR . '/help/helpsites.xml', $data))
		{
			throw new \Exception(Text::_('COM_CONFIG_ERROR_HELPREFRESH_ERROR_STORE'), 500);
		}

		$options = array_merge(
			array(
				HTMLHelper::_('select.option', '', Text::_('JOPTION_USE_DEFAULT'))
			),
			Help::createSiteList(JPATH_ADMINISTRATOR . '/help/helpsites.xml')
		);

		echo new JsonResponse($options);
		Factory::getApplication()->close();
	}
}
