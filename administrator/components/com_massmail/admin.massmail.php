<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Massmail
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

/*
 * Make sure the user is authorized to view this page
 */
$user = & JFactory::getUser();
if (!$user->authorize('core.massmail.manage')) {
	$mainframe->redirect('index.php', JText::_('ALERTNOTAUTH'));
}

require_once(JApplicationHelper::getPath('admin_html'));

switch ($task)
{
	case 'send':
		sendMail();
		break;

	case 'cancel':
		$mainframe->redirect('index.php');
		break;

	default:
		messageForm($option);
		break;
}

function messageForm($option)
{
	$lists['group'] = JHtml::_('access.usergroup', 'mm_group', 0, 'size="10"', true);
	HTML_massmail::messageForm($lists, $option);
}

function sendMail()
{
	global $mainframe;

	// Check for request forgeries
	JRequest::checkToken() or jexit('Invalid Token');

	$db					= &JFactory::getDbo();
	$user 				= &JFactory::getUser();
	$acl 				= &JFactory::getACL();

	$mode				= JRequest::getVar('mm_mode', 0, 'post', 'int');
	$subject			= JRequest::getVar('mm_subject', '', 'post', 'string');
	$gou				= JRequest::getVar('mm_group', '0', 'post', 'int');
	$recurse			= JRequest::getVar('mm_recurse', 'NO_RECURSE', 'post', 'word');
	$bcc				= JRequest::getVar('mm_bcc', 0, 'post', 'int');

	// pulls message inoformation either in text or html format
	if ($mode) {
		$message_body	= JRequest::getVar('mm_message', '', 'post', 'string', JREQUEST_ALLOWRAW);
	} else {
		// automatically removes html formatting
		$message_body	= JRequest::getVar('mm_message', '', 'post', 'string');
	}

	// Check for a message body and subject
	if (!$message_body || !$subject) {
		$mainframe->redirect('index.php?option=com_massmail', JText::_('Please fill in the form correctly'));
	}

	// get users in the group out of the acl
	$to = $acl->get_group_objects($gou, 'ARO', $recurse);
	JArrayHelper::toInteger($to['users']);

	// Get sending email address
	/*
	$query = 'SELECT email'
	. ' FROM #__users'
	. ' WHERE id = '.(int) $user->get('id')
	;
	$db->setQuery($query);
	$user->set('email', $db->loadResult());
	*/

	// Get all users email and group except for senders
	$query = 'SELECT email'
	. ' FROM #__users'
	. ' WHERE id != '.(int) $user->get('id')
	. ($gou !== 0 ? ' AND id IN (' . implode(',', $to['users']) . ')' : '')
	;

	$db->setQuery($query);
	$rows = $db->loadObjectList();

	// Check to see if there are any users in this group before we continue
	if (! count($rows)) {
		$msg	= JText::_('No users could be found in this group.');
		$mainframe->redirect('index.php?option=com_massmail', $msg);
	}

	$mailer = &JFactory::getMailer();
	$params = &JComponentHelper::getParams('com_massmail');

	// Build e-mail message format
	$mailer->setSender(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('fromname')));
	$mailer->setSubject($params->get('mailSubjectPrefix') . stripslashes($subject));
	$mailer->setBody($message_body . $params->get('mailBodySuffix'));
	$mailer->IsHTML($mode);

	// Add recipients

	if ($bcc) {
		foreach ($rows as $row) {
			$mailer->addBCC($row->email);
		}
		$mailer->addRecipient($mainframe->getCfg('mailfrom'));
	}else {
		foreach ($rows as $row) {
 			$mailer->addRecipient($row->email);
 		}
	}

	// Send the Mail
	$rs	= $mailer->Send();

	// Check for an error
	if (JError::isError($rs)) {
		$msg	= $rs->getError();
	} else {
		$msg = $rs ? JText::sprintf('E-mail sent to', count($rows)) : JText::_('The mail could not be sent');
	}

	// Redirect with the message
	$mainframe->redirect('index.php?option=com_massmail', $msg);

}
