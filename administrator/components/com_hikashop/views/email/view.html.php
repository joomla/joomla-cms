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
class EmailViewEmail extends hikashopView
{
	var $type = '';
	var $ctrl= 'email';
	var $nameListing = 'EMAILS';
	var $nameForm = 'EMAILS';
	var $icon = 'inbox';

	function display($tpl = null) {
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		return parent::display($tpl);
	}

	function form(){
		$mail_name = JRequest::getString('mail_name');
		$mailClass = hikashop_get('class.mail');
		$data = true;
		$mail = $mailClass->get($mail_name,$data);
		if(empty($mail)){
			$config =& hikashop_config();
			$mail->from_name = $config->get('from_name');
			$mail->from_email = $config->get('from_email');
			$mail->reply_name = $config->get('reply_name');
			$mail->reply_email = $config->get('reply_email');
			$mail->subject = '';
			$mail->html = 1;
			$mail->published = 1;
			$mail->body = '';
			$mail->altbody = '';
			$mail->preload = '';
			$mail->mail = $mail_name;
			$mail->email_log_published = 1;
		};
		$tabs = hikashop_get('helper.tabs');
		$values = new stdClass();
		$values->maxupload = (hikashop_bytes(ini_get('upload_max_filesize')) > hikashop_bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize');
		$toggleClass = hikashop_get('helper.toggle');
		$js = '
function updateEditor(htmlvalue){
	if(htmlvalue == "0"){window.document.getElementById("htmlfieldset").style.display = "none"}else{window.document.getElementById("htmlfieldset").style.display = "block"; }
}
window.addEvent("load", function(){ updateEditor('.$mail->html.'); });';
		$script = '
function addFileLoader(){
	var divfile=window.document.getElementById("loadfile");
	var input = document.createElement("input");
	input.type = "file";
	input.size = "30";
	input.name = "attachments[]";
	divfile.appendChild(document.createElement("br"));
	divfile.appendChild(input);
}
function submitbutton(pressbutton){
	if (pressbutton == "cancel") {
		submitform( pressbutton );
		return;
	}
	if(window.document.getElementById("subject").value.length < 2){alert("'.JText::_('ENTER_SUBJECT',true).'"); return false;}
	submitform(pressbutton);
}
';
		if (!HIKASHOP_PHP5) {
			$doc =& JFactory::getDocument();
		}else{
			$doc = JFactory::getDocument();
		}
		$doc->addScriptDeclaration( $js.$script );
		if(JRequest::getString('tmpl')!='component'){
			$this->toolbar = array(
				'save',
				'apply',
				'cancel',
				'|',
				array('name' => 'pophelp', 'target' => $this->ctrl.'-form')
			);

			hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task=edit&mail_name='.$mail_name);
		}
		$this->assignRef('toggleClass',$toggleClass);
		$this->assignRef('values',$values);
		$this->assignRef('mail_name',$mail_name);
		$this->assignRef('mail',$mail);
		$this->assignRef('tabs',$tabs);
		$editor = hikashop_get('helper.editor');
		$this->assignRef('editor',$editor);
	}

	function listing(){
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$config =& hikashop_config();

		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );

		$pageInfo->limit->value = $app->getUserStateFromRequest($this->paramBase.'.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($this->paramBase.'.limitstart', 'limitstart', 0, 'int');
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.user_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );

		jimport('joomla.filesystem.file');
		$mail_folder = rtrim( str_replace( '{root}', JPATH_ROOT, $config->get('mail_folder',HIKASHOP_MEDIA.'mail'.DS)), '/\\').DS;

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
			'waitlist_admin_notification',
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
			$email->published = $config->get($file.'.published');
			$emails[] = $email;
		}

		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = count($emails);

		$emails = array_slice($emails, $pageInfo->limit->start, $pageInfo->limit->value);
		$pageInfo->elements->page = count($emails);

		$this->assignRef('rows',$emails);
		$this->assignRef('pageInfo',$pageInfo);
		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);
		$this->getPagination();

		$this->toolbar = array(
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);

		$manage = hikashop_isAllowed($config->get('acl_email_manage','all'));
		$this->assignRef('manage',$manage);
		$delete = hikashop_isAllowed($config->get('acl_email_delete','all'));
		$this->assignRef('delete',$delete);

		jimport('joomla.client.helper');
		$ftp = JClientHelper::setCredentialsFromRequest('ftp');
		$this->assignRef('ftp',$ftp);
		$toggle = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggle);
	}
}
