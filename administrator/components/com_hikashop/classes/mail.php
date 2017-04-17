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
class hikashopMailClass {
	var $mail_success = true;
	var $_force_embed = false;
	var $mail_folder = '';

	function __construct(){
		$this->mailer = JFactory::getMailer();
	}

	function get($name,&$data){
		$this->mailer = JFactory::getMailer();
		$mail = new stdClass();
		$mail->mail_name = $name;
		$this->loadInfos($mail,$name);
		$mail->body = $this->loadEmail($mail,$data);
		$mail->altbody = $this->loadEmail($mail,$data,'text');
		$mail->preload = $this->loadEmail($mail,$data,'preload');
		$mail->data =& $data;
		if($data!==true) $mail->body= hikashop_absoluteURL($mail->body);
		if(empty($mail->altbody)&&$data!==true) $mail->altbody = $this->textVersion($mail->body);

		return $mail;
	}

	function loadInfos(&$mail, $name){
		$config =& hikashop_config();
		$mail->from_name = $config->get($name.'.from_name');
		$mail->from_email = $config->get($name.'.from_email');
		$mail->reply_name = $config->get($name.'.reply_name');
		$mail->reply_email = $config->get($name.'.reply_email');
		$mail->bcc_email = $config->get($name.'.bcc_email');
		$mail->cc_email = $config->get($name.'.cc_email');
		$mail->subject = $config->get($name.'.subject');
		$mail->html = $config->get($name.'.html');
		$mail->published = $config->get($name.'.published',1);
		$mail->email_log_published = $config->get($name.'.email_log_published',1);

		$attach = $config->get($name.'.attach');
		if(empty($attach)){
			$attach = array();
		}else{
			$attach = unserialize($attach);
		}
		$mail->attach=$attach;
		if(empty($mail->from_name)){
			$mail->from_name = $config->get('from_name');
		}
		if(empty($mail->from_email)){
			$mail->from_email = $config->get('from_email');
		}
		if(empty($mail->reply_name)){
			$mail->reply_name = $config->get('reply_name');
		}
		if(empty($mail->reply_email)){
			$mail->reply_email = $config->get('reply_email');
		}
		if(empty($mail->html)){
			$mail->html = $config->get('html');
		}
	}

	function saveForm(){
		$app = JFactory::getApplication();
		$mail = new stdClass();
		$config =& hikashop_config();

		$mail->mail_name = JRequest::getString('mail_name');
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = & JFilterInput::getInstance(null, null, 1, 1);
		foreach($formData['mail'] as $column => $value){
			hikashop_secureField($column);
			if(in_array($column,array('params','body','altbody','preload'))){
				$mail->$column = $value;
			}else{
				$mail->$column = $safeHtmlFilter->clean(strip_tags($value), 'string');
			}
		}

		$mail->attach = array();
		$old = $config->get($mail->mail_name.'.attach');
		if(!empty($old)){
			$oldAttachments = unserialize($old);
			foreach($oldAttachments as $oldAttachment){
				$mail->attach[] = $oldAttachment;
			}
		}

		$attachments = JRequest::getVar( 'attachments', array(), 'files', 'array' );

		if(!empty($attachments['name'][0]) || !empty($attachments['name'][1])){
			jimport('joomla.filesystem.file');
			$allowedFiles = explode(',',strtolower($config->get('allowedfiles')));
			$uploadFolder = JPath::clean(html_entity_decode($config->get('uploadfolder')));
			if(!preg_match('#^([A-Z]:)?/.*#',$uploadFolder)){
				$uploadFolder = trim($uploadFolder,DS.' ').DS;
				$uploadFolder = JPath::clean(HIKASHOP_ROOT.$uploadFolder);
			}
			if(!is_dir($uploadFolder)){
				jimport('joomla.filesystem.folder');
				JFolder::create($uploadFolder);
			}
			if(!is_writable($uploadFolder)){
				@chmod($uploadFolder,'0755');
				if(!is_writable($uploadFolder)){
					$app->enqueueMessage(JText::sprintf( 'WRITABLE_FOLDER',$uploadFolder), 'notice');
				}
			}

			foreach($attachments['name'] as $id => $filename){
				if(empty($filename)) continue;
				$attachment = new stdClass();
				$attachment->filename = strtolower(JFile::makeSafe($filename));
				$attachment->size = $attachments['size'][$id];
				if(!preg_match('#\.('.str_replace(array(',','.'),array('|','\.'),$config->get('allowedfiles')).')$#Ui',$attachment->filename,$extension) || preg_match('#\.(php.?|.?htm.?|pl|py|jsp|asp|sh|cgi)$#Ui',$attachment->filename)){
					$app->enqueueMessage(JText::sprintf( 'ACCEPTED_TYPE',substr($attachment->filename,strrpos($attachment->filename,'.')+1),$config->get('allowedfiles')), 'notice');
					continue;
				}
				$attachment->filename = str_replace(array('.',' '),'_',substr($attachment->filename,0,strpos($attachment->filename,$extension[0]))).$extension[0];
				if ( !move_uploaded_file($attachments['tmp_name'][$id], $uploadFolder . $attachment->filename)) {
					if(!JFile::upload($attachments['tmp_name'][$id], $uploadFolder . $attachment->filename)){
						$app->enqueueMessage(JText::sprintf( 'FAIL_UPLOAD',$attachments['tmp_name'][$id],$uploadFolder . $attachment->filename), 'error');
						continue;
					}
				}
				$mail->attach[] = $attachment;

			}
		}

		return $this->save($mail);
	}

	function save(&$element){
		if(empty($element->mail_name))
			return false;

		$configData = array();
		if(isset($element->body)){
			$this->saveEmail($element->mail_name,$element->body,'html');
		}
		if(isset($element->altbody)){
			$this->saveEmail($element->mail_name,$element->altbody,'text');
		}
		if(isset($element->preload)){
			$this->saveEmail($element->mail_name,$element->preload,'preload');
		}
		if(isset($element->from_name)){
			$configData[$element->mail_name.'.from_name']=$element->from_name;
		}
		if(isset($element->from_email)){
			$configData[$element->mail_name.'.from_email']=$element->from_email;
		}
		if(isset($element->reply_name)){
			$configData[$element->mail_name.'.reply_name']=$element->reply_name;
		}
		if(isset($element->reply_email)){
			$configData[$element->mail_name.'.reply_email']=$element->reply_email;
		}
		if(isset($element->bcc_email)){
			$configData[$element->mail_name.'.bcc_email']=$element->bcc_email;
		}
		if(isset($element->cc_email)){
			$configData[$element->mail_name.'.cc_email']=$element->cc_email;
		}
		if(isset($element->subject)){
			$configData[$element->mail_name.'.subject']=$element->subject;
		}
		if(isset($element->html)){
			$configData[$element->mail_name.'.html']=$element->html;
		}
		if(isset($element->attach)){
			$configData[$element->mail_name.'.attach']=serialize($element->attach);
		}
		if(isset($element->published)){
			$configData[$element->mail_name.'.published']=(int)$element->published;
		}
		if(isset($element->email_log_published)){
			$configData[$element->mail_name.'.email_log_published']=$element->email_log_published;
		}

		$config =& hikashop_config();
		$config->save($configData);
		return $element->mail_name;
	}

	function delete(&$mail_name, $type = '') {
		$path = $this->getMailPath($mail_name, $type, true);
		if(empty($path))
			return false;

		jimport('joomla.filesystem.file');
		if(file_exists($path)){
			return JFile::delete($path);
		}
		return true;
	}

	function saveEmail($name,$data,$type='html'){
		$path = $this->getMailPath($name, $type, true);
		if(empty($path))
			$path = $this->mail_folder.$name.'.'.$type.'.modified.php';

		jimport('joomla.filesystem.file');
		if(file_exists($path)){
			JFile::delete($path);
		}
		return JFile::write($path,$data);
	}

	function loadEmail(&$mail,&$data,$type='html') {
		$path = $this->getMailPath($mail->mail_name, $type);
		if(empty($path))
			return '';

		if($data === true) {
			jimport('joomla.filesystem.file');
			return JFile::read($path);
		}

		$preload = $this->getMailPath($mail->mail_name, 'preload');
		$postload = $this->getMailPath($mail->mail_name, 'postload');

		if($mail->mail_name=='order_status_notification' && !empty($data->order_status)){
			$pathWithStatus = $this->getMailPath($mail->mail_name.'.'.$data->order_status, $type);
			if(!empty($pathWithStatus)) $path = $pathWithStatus;
			$preloadWithStatus = $this->getMailPath($mail->mail_name.'.'.$data->order_status, 'preload');
			if(!empty($pathWithStatus)) $preload = $preloadWithStatus;
			$postloadWithStatus = $this->getMailPath($mail->mail_name.'.'.$data->order_status, 'postload');
			if(!empty($pathWithStatus)) $postload = $postloadWithStatus;
		}

		$currencyHelper = hikashop_get('class.currency');

		ob_start();

		$useTemplating = false;
		$vars = array();
		$texts = array();
		$templates = array();

		if(!empty($preload) && file_exists($preload))
			include $preload;

		if($useTemplating || !empty($vars) || !empty($texts) || !empty($templates))
			ob_start();

		require($path);

		if(!empty($postload) && file_exists($postload)) {
			include $postload;
		} else if($useTemplating || !empty($vars) || !empty($texts) || !empty($templates)) {
			echo $this->processMailTemplate($mail, $data, $texts, $vars, $templates);
		}

		return ob_get_clean();
	}

	function processMailTemplate(&$mail, &$data, $texts, $vars, $templates = array()) {
		$content = ob_get_clean();

		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onHkProcessMailTemplate', array(&$mail, &$data, &$content, &$vars, &$texts, &$templates));

		if(!empty($templates)) {
			foreach($templates as $key => $templateVariables) {
				$cursorStartLength = strlen('<!--{START:'.$key.'}-->');
				$cusorEndLength = strlen('<!--{END:'.$key.'}-->');

				$cursorStart = strpos($content, '<!--{START:'.$key.'}-->');
				$cursorEnd = strpos($content, '<!--{END:'.$key.'}-->');

				while($cursorStart !== false && $cursorEnd !== false) {
					$templateData = '';
					$templateContent = '';
					if($cursorStart !== false && $cursorEnd !== false) {
						$templateContent = substr($content, $cursorStart + $cursorStartLength, $cursorEnd - $cursorStart - $cursorStartLength);
						if(strpos($content, '{VAR:'.$key.'}') === false)
							$content = substr($content, 0, $cursorStart) . '{VAR:'.$key.'}' . substr($content, $cursorEnd + $cusorEndLength);
						else
							$content = substr($content, 0, $cursorStart) . substr($content, $cursorEnd + $cusorEndLength);
					}
					if(!empty($templateContent) && !empty($templateVariables)) {
						foreach($templateVariables as $c) {
							$d = $templateContent;
							foreach($c as $k => $v) {
								if(is_string($v) || is_int($v) || is_float($v)) {
									$d = str_replace('{LINEVAR:'.$k.'}', $v, $d);
								} else if(is_object($v) || is_array($v)) {
									foreach($v as $objK => $objV) {
										if(is_string($objV) || is_int($objV) || is_float($objV))
											$d = str_replace('{LINEVAR:'.$k.'.'.$objK.'}', $objV, $d);
									}
								}
							}
							$templateData .= $d;
						}
						$content = str_replace('{VAR:'.$key.'}', $templateData, $content);
					}
					$cursorStart = strpos($content, '<!--{START:'.$key.'}-->');
					$cursorEnd = strpos($content, '<!--{END:'.$key.'}-->');
				}
			}
		}

		foreach($texts as $k => $v) {
			$content = str_replace('{TXT:'.$k.'}', $v, $content);
		}

		foreach($vars as $k => $v) {
			if(is_string($v) || is_int($v) || is_float($v))
				$content = str_replace('{VAR:'.$k.'}', $v, $content);
			else if(is_object($v) || is_array($v)) {
				foreach($v as $objK => $objV) {
					if(is_string($objV) || is_int($objV) || is_float($objV))
						$content = str_replace('{VAR:'.$k.'.'.$objK.'}', $objV, $content);
				}
			}

			$startIf = '<!--{IF:'.$k.'}-->';
			$endIf = '<!--{ENDIF:'.$k.'}-->';
			if(empty($v)) {
				$cursorStart = strpos($content, $startIf);
				$cursorEnd = strpos($content, $endIf);
				if($cursorStart !== false && $cursorEnd !== false) {
					$content = substr($content, 0, $cursorStart) . substr($content, $cursorEnd + strlen($endIf));
				} else {
					$content = str_replace(array($startIf, $endIf), '', $content);
				}
			} else {
				$content = str_replace(array($startIf, $endIf), '', $content);
			}
		}
		if(strpos($content, '<!--{IF:') !== false) {
			$out = array();
			if(preg_match_all('#<!--{IF:([- _A-Z0-9a-z]+)}-->#U', $content, $out)) {
				foreach($out[1] as $key) {
					$startIf = '<!--{IF:'.$key.'}-->';
					$endIf = '<!--{ENDIF:'.$key.'}-->';
					$cursorStart = strpos($content, $startIf);
					$cursorEnd = strpos($content, $endIf);
					if($cursorStart !== false && $cursorEnd !== false) {
						$content = substr($content, 0, $cursorStart) . substr($content, $cursorEnd + strlen($endIf));
					} else {
						$content = str_replace(array($startIf, $endIf), '', $content);
					}
				}
			}
		}

		if(strpos($content, '{TXT:') !== false) {
			$out = array();
			if(preg_match_all('#{TXT:([- _A-Z0-9a-z]+)}#U', $content, $out)) {
				foreach($out[1] as $key) {
					if(isset($texts[$key]))
						$content = str_replace('{TXT:'.$key.'}', $texts[$key], $content);
					else
						$content = str_replace('{TXT:'.$key.'}', JText::_($key), $content);
				}
			}
		}

		if(strpos($content, '{VAR:') !== false) {
			$out = array();
			if(preg_match_all('#{VAR:([-. _A-Z0-9a-z]+)}#U', $content, $out)) {
				foreach($out[1] as $key) {
					$content = str_replace('{VAR:'.$key.'}', '', $content);
				}
			}
		}

		if(strpos($content, '{LINEVAR:') !== false) {
			$out = array();
			if(preg_match_all('#{LINEVAR:([-. _A-Z0-9a-z]+)}#U', $content, $out)) {
				foreach($out[1] as $key) {
					$content = str_replace('{LINEVAR:'.$key.'}', '', $content);
				}
			}
		}

		return $content;
	}

	function getMailPath($mail_name, $type = 'html', $getModifiedFile = false) {
		if(empty($this->mail_folder)) {
			$config = hikashop_config();
			$this->mail_folder = rtrim( str_replace( '{root}', JPATH_ROOT, $config->get('mail_folder',HIKASHOP_MEDIA.'mail'.DS)), '/\\').DS;
		}
		if(empty($this->mail_folder)) { $this->mail_folder = HIKASHOP_MEDIA.'mail'.DS; }

		if(!file_exists($this->mail_folder.$mail_name.'.'.$type.'.php')) {

			$plugin_files = array();
			JPluginHelper::importPlugin('hikashop');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onMailListing', array(&$plugin_files));
			if(!empty($plugin_files)) {
				$mail_folder = '';
				$mail_file = '';
				foreach($plugin_files as $plugin_file) {
					if($plugin_file['file'] == $mail_name) {
						$mail_folder = @$plugin_file['folder'];
						$mail_file = $plugin_file['filename'];
						break;
					}
				}
				if(!empty($mail_file)) {
					if(empty($mail_folder))
						$mail_folder = $this->mail_folder;

					$path = $mail_folder.$mail_file.'.'.$type.'.modified.php';
					if(!file_exists($path)) {
						$path = $mail_folder . $mail_file.'.'.$type.'.php';
						if(!file_exists($path)) {
							return '';
						}
						if($getModifiedFile)
							return $path = $mail_folder.$mail_file.'.'.$type.'.modified.php';
					}
					return $path;
				}
			}
		}

		$path = $this->mail_folder.$mail_name.'.'.$type.'.modified.php';
		if(!file_exists($path)){
			$path = $this->mail_folder.$mail_name.'.'.$type.'.php';
			if(!file_exists($path)){
				return '';
			}
			if($getModifiedFile)
				return $this->mail_folder.$mail_name.'.'.$type.'.modified.php';
			return $path;
		}
		return $path;
	}

	function sendMail(&$mail){
		if(empty($mail))
			return false;
		if(isset($mail->published) && !$mail->published)
			return true;

		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$mail->mail_success = false;
		$dispatcher->trigger('onBeforeMailPrepare', array(&$mail, &$this->mailer, &$do) );
		if(!$do) {
			if($mail->mail_success == true)
				$this->mail_success = true;
			else
				$this->mail_success = false;
			return false;
		}

		$config =& hikashop_config();

		$this->mailer->CharSet = $config->get('charset');
		if(empty($this->mailer->CharSet)) $this->mailer->CharSet = 'utf-8';

		$this->mailer->Encoding = $config->get('encoding_format');
		if(empty($this->mailer->Encoding)) $this->mailer->Encoding = 'base64';

		$this->mailer->WordWrap = intval($config->get('word_wrapping',0));

		$this->mailer->Sender = $this->cleanText($config->get('bounce_email'));
		if(empty($this->mailer->Sender)) $this->mailer->Sender = '';

		if(!empty($mail->dst_email)){
			if(is_array($mail->dst_email)){
				$this->mailer->addRecipient($mail->dst_email);
			}else{
				if(strpos($mail->dst_email,',')){
					$mail->dst_email = explode(',',$mail->dst_email);
					$this->mailer->addRecipient($mail->dst_email);
				}else{
					$addedName = $config->get('add_names',true) ? $this->cleanText(@$mail->dst_name) : '';
					$this->mailer->AddAddress($this->cleanText($mail->dst_email),$addedName);
				}
			}
		}
		if(!empty($mail->cc_email)) {
			if(!is_array($mail->cc_email))
				$mail->cc_email = explode(',', $mail->cc_email);
			$this->mailer->addCC($mail->cc_email);
		}
		if(!empty($mail->bcc_email)) {
			if(!is_array($mail->bcc_email))
				$mail->bcc_email = explode(',', $mail->bcc_email);
			$this->mailer->addBCC($mail->bcc_email);
		}
		$this->setFrom($mail->from_email,@$mail->from_name);
		if(!empty($mail->reply_email)){
			$replyToName = $config->get('add_names',true) ? $this->cleanText(@$mail->reply_name) : '';
			$this->mailer->AddReplyTo(array($this->cleanText($mail->reply_email),$replyToName));
		}
		$this->mailer->setSubject($mail->subject);
		$this->mailer->IsHTML(@$mail->html);
		if(!empty($mail->html)){
			$style = '';
			if(isset($mail->htmlStyle)) {
				$style = '<style type="text/css">'."\r\n".$mail->htmlStyle."\r\n".'</style>';
			}
			$htmlExtra = '';
			$lang = JFactory::getLanguage();
			if($lang->isRTL()) {
				$htmlExtra = ' dir="rtl"';
			}
			$this->mailer->Body = '<html><head>'.
				'<meta http-equiv="Content-Type" content="text/html; charset='.$this->mailer->CharSet.'">'.
				'<title>'.$mail->subject.'</title>'.$style.
				'<meta name="viewport" content="width=device-width, initial-scale=1.0">'.
				'</head><body class="hikashop_mail"'.$htmlExtra.'>'.hikashop_absoluteURL($mail->body).'</body></html>';
			if($config->get('multiple_part',false)){
				if(empty($mail->altbody)){
					$this->mailer->AltBody = $this->textVersion($mail->body);
				}else{
					$this->mailer->AltBody = $mail->altbody;
				}
			}
		}else{
			if(empty($mail->altbody)&&!empty($mail->body))
				$mail->altbody = $this->textVersion($mail->body);
			$this->mailer->Body = $mail->altbody;
		}

		if(empty($mail->attachments)&&!empty($mail->mail_name)){
			$mail->attachments=$this->loadAttachments($mail->mail_name);
		}

		if(!empty($mail->attachments)){
			if($config->get('embed_files') || $this->_force_embed){
				foreach($mail->attachments as $attachment){
					$this->mailer->AddAttachment($attachment->filename);
				}
			}else{
				$attachStringHTML = '<br/><fieldset><legend>'.JText::_( 'ATTACHMENTS' ).'</legend><table>';
				$attachStringText = "\n"."\n".'------- '.JText::_( 'ATTACHMENTS' ).' -------';
				foreach($mail->attachments as $attachment){
					$attachStringHTML .= '<tr><td><a href="'.$attachment->url.'" target="_blank">'.$attachment->name.'</a></td></tr>';
					$attachStringText .= "\n".'-- '.$attachment->name.' ( '.$attachment->url.' )';
				}
				$attachStringHTML .= '</table></fieldset>';
				if(@$mail->html){
					$this->mailer->Body = str_replace('</body></html>',$attachStringHTML.'</body></html>',$this->mailer->Body);
					if(!empty($this->mailer->AltBody))
						$this->mailer->AltBody .= "\n".$attachStringText;
				}else{
					$this->mailer->Body .= $attachStringText;
				}
			}
		}
		if((bool)$config->get('embed_images',0)){
			$this->embedImages();
		}

		$dispatcher->trigger('onBeforeMailSend', array(&$mail, &$this->mailer) );

		if(strtoupper($this->mailer->CharSet) != 'UTF-8'){
			$encodingHelper = hikashop_get('helper.encoding');
			$this->mailer->Body = $encodingHelper->change($this->mailer->Body,'UTF-8',$this->mailer->CharSet);
			$this->mailer->Subject = $encodingHelper->change($this->mailer->Subject,'UTF-8',$this->mailer->CharSet);
			if(!empty($this->mailer->AltBody))
				$this->mailer->AltBody = $encodingHelper->change($this->mailer->AltBody,'UTF-8',$this->mailer->CharSet);
		}
		$this->mailer->Body = str_replace(" ",' ',$this->mailer->Body);

		$result = $this->mailer->Send();
		if(!$result || !empty($result->message)) {
			$this->mail_success = false;
		}
		if(!empty($result->message)) {
		}

		return $result;
	}

	function loadAttachments($name){
		$config =& hikashop_config();
		$attach = $config->get($name.'.attach');
		if(empty($attach)){
			$attach = array();
		}else{
			$attachData = unserialize($attach);
			$uploadFolder = str_replace(array('/','\\'),DS,html_entity_decode($config->get('uploadfolder')));
			if(preg_match('#^([A-Z]:)?/.*#',$uploadFolder)){
				if(!$config->get('embed_files')){
					$this->_force_embed = true;
				}
				$uploadPath = str_replace(array('/','\\'),DS,$uploadFolder);
			}else{
				$uploadFolder = trim($uploadFolder,DS.' ').DS;
				$uploadPath = str_replace(array('/','\\'),DS,HIKASHOP_ROOT.$uploadFolder);
			}
			$uploadURL = HIKASHOP_LIVE.str_replace(DS,'/',$uploadFolder);
			$attach = array();
			foreach($attachData as $oneAttach){
				$attachObj = new stdClass();
				$attachObj->name = $oneAttach->filename;
				$attachObj->filename = $uploadPath.$oneAttach->filename;
				$attachObj->url = $uploadURL.$oneAttach->filename;
				$attach[] = $attachObj;
			}
		}
		return $attach;
	}
	function cleanText($text){
		return trim( preg_replace( '/(%0A|%0D|\n+|\r+)/i', '', (string) $text ) );
	}

	function setFrom($email,$name=''){
		if(!empty($email)){
			$this->mailer->From = $this->cleanText($email);
		}
		$config =& hikashop_config();
		if(!empty($name) AND $config->get('add_names',true)){
			$this->mailer->FromName = $this->cleanText($name);
		}
	}

	function textVersion($html){
		$html = hikashop_absoluteURL($html);
		$html = preg_replace('# +#',' ',$html);
		$html = str_replace(array("\n","\r","\t"),'',$html);
		$removeScript = "#< *script(?:(?!< */ *script *>).)*< */ *script *>#isU";
		$removeStyle = "#< *style(?:(?!< */ *style *>).)*< */ *style *>#isU";
		$removeStrikeTags =  '#< *strike(?:(?!< */ *strike *>).)*< */ *strike *>#iU';
		$replaceByTwoReturnChar = '#< *(h1|h2)[^>]*>#Ui';
		$replaceByStars = '#< *li[^>]*>#Ui';
		$replaceByReturnChar1 = '#< */ *(li|td|tr|div|p)[^>]*> *< *(li|td|tr|div|p)[^>]*>#Ui';
		$replaceByReturnChar = '#< */? *(br|p|h1|h2|h3|li|ul|h4|h5|h6|tr|td|div)[^>]*>#Ui';
		$replaceLinks = '/< *a[^>]*href *= *"([^"]*)"[^>]*>(.*)< *\/ *a *>/Uis';
		$text = preg_replace(array($removeScript,$removeStyle,$removeStrikeTags,$replaceByTwoReturnChar,$replaceByStars,$replaceByReturnChar1,$replaceByReturnChar,$replaceLinks),array('','','',"\n\n","\n* ","\n","\n",'${2} ( ${1} )'),$html);
		$text = str_replace(array(" ","&nbsp;"),' ',strip_tags($text));
		$text = trim(@html_entity_decode($text,ENT_QUOTES,'UTF-8'));
		$text = preg_replace('# +#',' ',$text);
		$text = preg_replace('#\n *\n\s+#',"\n\n",$text);
		return $text;
	}

	function embedImages(){
		preg_match_all('/(src|background)="([^"]*)"/Ui', $this->mailer->Body, $images);
		$result = true;
		if(!empty($images[2])) {
			$mimetypes = array(
				'bmp'   =>  'image/bmp',
				'gif'   =>  'image/gif',
				'jpeg'  =>  'image/jpeg',
				'jpg'   =>  'image/jpeg',
				'jpe'   =>  'image/jpeg',
				'png'   =>  'image/png',
				'tiff'  =>  'image/tiff',
				'tif'   =>  'image/tiff'
			);
			$allimages = array();
			foreach($images[2] as $i => $url) {
				if(isset($allimages[$url])) continue;
				$allimages[$url] = 1;
				$path      = str_replace(array(HIKASHOP_LIVE,'/'),array(HIKASHOP_ROOT,DS),$url);
				$filename  = basename($url);
				$md5       = md5($filename);
				$cid       = 'cid:' . $md5;
				$fileParts = explode(".", $filename);
				$ext       = strtolower($fileParts[1]);
				if(!isset($mimetypes[$ext])) continue;
				$mimeType  = $mimetypes[$ext];
				if($this->mailer->AddEmbeddedImage($path, $md5, $filename, 'base64', $mimeType)){
					 $this->mailer->Body = preg_replace("/".$images[1][$i]."=\"".preg_quote($url, '/')."\"/Ui", $images[1][$i]."=\"".$cid."\"", $this->mailer->Body);
				}else{
					$result = false;
				}
			}
		}
		return $result;
	}

	function sendMailEot(&$order, &$vars){
		$infos = new stdClass();
		$infos->order =& $order;
		$infos->vars =& $vars;
		$mail = $this->get('subscription_eot',$infos);
		$mail->subject = JText::sprintf($mail->subject,HIKASHOP_LIVE);

		$mail->dst_email = $vars['payer_email']; // for paypal
		$this->sendMail($mail);
		return;
	}
}
