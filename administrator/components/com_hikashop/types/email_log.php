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
class HikashopEmail_logType{
	function load($form){
		$this->values = array();
		if(!$form){
			$this->values[] = JHTML::_('select.option', 'all',JText::_('HIKA_ALL') );
		}

		jimport('joomla.filesystem.file');
		$mail_folder = HIKASHOP_MEDIA.'mail'.DS;

		$files = array(
			'cron_report',
			'order_admin_notification',
			'order_creation_notification',
			'order_status_notification',
			'order_notification',
			'user_account',
			'user_account_admin_notification',
			'out_of_stock',
			'order_cancel',
			'waitlist_notification',
			'new_comment',
			'contact_request',
			'subscription_eot',
			'massaction_notification'
		);

		$plugin_files = array();
		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onMailListing', array(&$plugin_files));
		if(!empty($plugin_files)) {
			$files = array_merge($files, $plugin_files);
		}

		$emails = array();
		foreach($files as $file){
			$folder = $mail_folder;
			$filename = $file;

			$email = new stdClass();

			if(is_array($file)) {
				$folder = $file['folder'];
				if(!empty($file['name']))
					$email->name = $file['name'];
				$filename = $file['filename'];
				$file = $file['file'];
			}

			$email->file = $file;
			$email->overriden_text = JFile::exists($folder.$filename.'.text.modified.php');
			$email->overriden_html = JFile::exists($folder.$filename.'.html.modified.php');
			$email->overriden_preload = JFile::exists($folder.$filename.'.preload.modified.php');
			$emails[] = $email;
			$this->values[] = JHTML::_('select.option', $file, JText::_($file));
		}
	}

	function display($map,$value,$form=false){
		$this->load($form);
		if(!$form){
			$options =' onchange="document.adminForm.submit();"';
		}
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1"'.$options, 'value', 'text', $value );
	}
}
