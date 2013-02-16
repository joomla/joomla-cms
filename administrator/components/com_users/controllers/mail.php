<?php
/**
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die;

/**
 * Users mail controller.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 */
class UsersControllerMail extends JControllerLegacy
{
	public function send()
	{
		// Check for request forgeries.
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('Mail');
		if ($model->send()) {
			$type = 'message';
		} else {
			$type = 'error';
		}

		$msg = $model->getError();
		$this->setredirect('index.php?option=com_users&view=mail', $msg, $type);
	}

	public function cancel()
	{
		// Check for request forgeries.
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
		$this->setRedirect('index.php');
	}
}
