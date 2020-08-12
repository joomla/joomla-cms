<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Users mail controller.
 *
 * @since  1.6
 */
class UsersControllerMail extends JControllerLegacy
{
	/**
	 * Send the mail
	 *
	 * @return void
	 *
	 * @since 1.6
	 */
	public function send()
	{
		// Redirect to admin index if mass mailer disabled in conf
		if (JFactory::getApplication()->get('massmailoff', 0) == 1)
		{
			JFactory::getApplication()->redirect(JRoute::_('index.php', false));
		}

		// Check for request forgeries.
		$this->checkToken('request');

		$model = $this->getModel('Mail');

		if ($model->send())
		{
			$type = 'message';
		}
		else
		{
			$type = 'error';
		}

		$msg = $model->getError();
		$this->setRedirect('index.php?option=com_users&view=mail', $msg, $type);
	}

	/**
	 * Cancel the mail
	 *
	 * @return void
	 *
	 * @since 1.6
	 */
	public function cancel()
	{
		// Check for request forgeries.
		$this->checkToken('request');

		// Clear data from session.
		\JFactory::getApplication()->setUserState('com_users.display.mail.data', null);

		$this->setRedirect('index.php');
	}
}
