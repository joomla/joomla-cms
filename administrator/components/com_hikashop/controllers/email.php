<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class emailController extends hikashopController{
	var $type='mail';
	function test(){
		$this->store();
		$config =& hikashop_config();
		$user = hikashop_loadUser(true);
		$mailClass = hikashop_get('class.mail');
		$addedName = $config->get('add_names',true) ? $mailClass->cleanText(@$user->name) : '';
		$mail = new stdClass();
		$mail->from_name = $config->get('from_name');
		$mail->from_email = $config->get('from_email');
		$mail->reply_name = $config->get('reply_name');
		$mail->reply_email = $config->get('reply_email');
		$mail->html = 0;
		$mailClass->AddAddress($user->user_email,$addedName);
		$mailClass->Subject = 'Test e-mail from '.HIKASHOP_LIVE;
		$mailClass->Body = 'This test email confirms that your configuration enables HikaShop to send emails normally.';
		$mail->debug = 1;
		$result = $mailClass->sendMail($mail);
		return $this->edit();
	}

	function remove(){
		$mail_name = JRequest::getCmd('mail_name');
		$type = JRequest::getCmd('type');
		$class = hikashop_get('class.'.$this->type);
		$num = $class->delete($mail_name,$type);
		$app = JFactory::getApplication();
		$app->enqueueMessage(JText::sprintf('SUCC_DELETE_ELEMENTS',$num), 'message');
		return $this->listing();
	}
}
