<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	MailTo
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class MailtoViewMailto extends JView
{
	function display($tpl = null)
	{
		$data = $this->getData();
		if ($data === false) {
			return false;
		}

		$this->set('data'  , $data);

		parent::display($tpl);
	}

	function &getData()
	{
		$user =& JFactory::getUser();
		$data = new stdClass();

		$data->link = urldecode( JRequest::getVar( 'link', '', 'method', 'base64' ) );
		if ($data->link == '') {
			JError::raiseError( 403, 'Link is missing' );
			$false = false;
			return $false;
		}

		// Load with previous data, if it exists
		$mailto				= JRequest::getVar( 'mailto', '', 'post' );
		$sender 			= JRequest::getVar( 'sender', '', 'post' );
		$from 				= JRequest::getVar( 'from', '', 'post' );
		$subject 			= JRequest::getVar( 'subject', '', 'post' );

		if ($user->get('id') > 0) {
			$data->sender	= $user->get('name');
			$data->from		= $user->get('email');
		}
		else
		{
			$data->sender	= $sender;
			$data->from		= $from;
		}

		$data->subject	= $subject;
		$data->mailto	= $mailto;

		return $data;
	}
}
?>
