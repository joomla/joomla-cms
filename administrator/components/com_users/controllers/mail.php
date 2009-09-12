<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * MassMail Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_massmail
 * @since		1.5
 */
class UsersControllerMassMail extends JController
{
	public function send()
	{
		// Check for request forgeries.
		JRequest::checkToken('request') or jexit(JText::_('JInvalid_Token'));

		$model = &$this->getModel('MassMail');
		if ($model->send()) {
			$type = 'message';
		} else {
			$type = 'error';
		}
		
		$msg = &$model->getError();		
		$this->setredirect('index.php?option=com_users&view=massmail', $msg, $type);
	}

	public function cancel()
	{
		// Check for request forgeries.
		JRequest::checkToken('request') or jexit(JText::_('JInvalid_Token'));
		$this->setRedirect('index.php?option=com_users&view=massmail.php');
	}
}