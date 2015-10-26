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
class hikashopToolbarHelper {
	var $aliases;

	function __construct() {
		$this->aliases = array();
	}

	function process($toolbar) {
		$config =& hikashop_config();
		if (!HIKASHOP_PHP5) {
			$bar =& JToolBar::getInstance('toolbar');
		}else{
			$bar = JToolBar::getInstance('toolbar');
		}
		foreach($toolbar as $tool) {
			if(!empty($tool) && is_string($tool)) {
				$tool = array('name' => $tool);
			}
			if(empty($tool['name']) || (isset($tool['display']) && $tool['display'] === false)) {
				continue;
			}
			$toolname = $tool['name'];
			$standard = array(
				'addNew' => array('new','add','New',false),
				'makeDefault' => array('default','default','Default',false),
				'assign' => array('assign','assign','Assign',false),
				'publish' => array('publish','publish','Publish',false),
				'publishList' => array('publish','publish','Publish',true),
				'editList' => array('edit','edit','Edit',true),
				'unpublish' => array('unpublish','unpublish','Unpublish',false),
				'unpublishList' => array('unpublish','unpublish','Unpublish',true),
				'trash' => array('trash','remove','Trash',true),
				'apply' => array('apply','apply','Apply',false),
				'copy' => array('copy','copy','HIKA_COPY',true),
				'save' => array('save','save','Save',false),
				'save2new' => array('save-new','save2new','JTOOLBAR_SAVE_AND_NEW',false),
				'save2copy' => array('save-copy','save2copy','JTOOLBAR_SAVE_AS_COPY',false),
				'cancel' => array('cancel','cancel','Cancel',false)
			);

			if(isset($standard[$toolname])) {
				$icon = $standard[$toolname][0];
				$task = $standard[$toolname][1];
				$alt = $standard[$toolname][2];
				if(HIKASHOP_J16 && substr($alt, 0, 5) != 'JTOOL' && substr($alt, 0, 5) != 'HIKA_') {
					$alt = 'JTOOLBAR_' . strtoupper($alt);
				}
				$check = $standard[$toolname][3];
				if(!empty($tool['icon'])) {
					$icon = $tool['icon'];
				}
				if(!empty($tool['task'])) {
					$task = $tool['task'];
				}
				if(isset($tool['alt'])) {
					$alt = $tool['alt'];
				}
				if(isset($tool['check'])) {
					$check = $tool['check'];
				}
				$bar->appendButton('Standard', $icon, $alt, $task, $check, false);
				continue;
			}

			$ret = $this->customTool($bar, strtolower($toolname), $tool);

			if(!$ret) {
				switch(strtolower($toolname)) {
					case '-':
						$width = '';
						if(!empty($tool['width'])) $width = (int)$tool['width'];
						$bar->appendButton('Separator', 'spacer', $width);
						break;
					case '|':
						$bar->appendButton('Separator', 'divider');
						break;
					case 'deletelist':
						$tool = array_merge(array('task'=>'remove','alt'=>'HIKA_DELETE','msg'=>'','confirm'=>true), $tool);
						if($tool['confirm'] && empty($tool['msg']))
							$tool['msg'] = JText::_('HIKA_VALIDDELETEITEMS');
						if(!empty($tool['msg'])) {
							$bar->appendButton('Confirm', $tool['msg'], 'delete', $tool['alt'], $tool['task'], true);
						} else {
							$bar->appendButton('Standard', 'delete', $tool['alt'], $tool['task'], true);
						}
						break;
					case 'custom':
						$tool = array_merge(array('icon'=>'','task'=>'','alt'=>'','check'=>true,'hide'=>false), $tool);
						$bar->appendButton('Standard', $tool['icon'], $tool['alt'], $tool['task'], $tool['check'], $tool['hide']);
						break;
					case 'confirm':
						$tool = array_merge(array('icon'=>'','task'=>'','alt'=>'','check'=>true,'hide'=>false,'msg'=>''), $tool);
						$bar->appendButton('Confirm',$tool['msg'], $tool['icon'], $tool['alt'], $tool['task'], $tool['check'], $tool['hide']);
						break;
					case 'preview':
						if(!empty($tool['target']) || !empty($tool['url'])) {
							$url = '';
							if(!empty($tool['target'])) $url = $tool['target'];
							if(!empty($tool['url'])) $url = $tool['url'];
							$bar->appendButton('Popup', 'preview', 'Preview', $url.'&task=preview');
						}
						break;
					case 'preferences':
						$tool = array_merge(array('component'=>'com_hikashop','path'=>''), $tool);
						$component = urlencode($tool['component']);
						$path = urlencode($tool['path']);
						if(HIKASHOP_J30){
							$uri = (string) JUri::getInstance();
							$return = urlencode(base64_encode($uri));
							$bar->appendButton('Link', 'options', 'JToolbar_Options', 'index.php?option=com_config&amp;view=component&amp;component=' . $component . '&amp;path=' . $path . '&amp;return=' . $return);
						}else{
							$top = 0;
							$left = 0;
							$height = '550';
							$width = '875';
							$bar->appendButton('Popup', 'options', 'JToolbar_Options', 'index.php?option=com_config&amp;view=component&amp;component='.$component.'&amp;path='.$path.'&amp;tmpl=component', $width, $height, $top, $left, '');
						}
						break;
					case 'help':
						break;
					case 'back':
						break;
					case 'link':
						$tool = array_merge(array('icon'=>'','url'=>'','alt'=>''), $tool);
						$bar->appendButton('Link', $tool['icon'], $tool['alt'], $tool['url']);
						break;
					case 'popup':
						$tool = array_merge(array('icon'=>'','url'=>'','alt'=>'','width'=>640,'height'=>480,'top'=>0,'left'=>0,'onClose'=>'','title'=>'','footer'=>''), $tool);
						if(HIKASHOP_J30) {
							if(!empty($tool['id']))
								$tool['icon'] = $tool['id'] . '#' . $tool['icon'];
							else
								$tool['icon'] = $tool['icon'] . '#' . $tool['icon'];
						}
						$bar->appendButton('HikaPopup', $tool['icon'], $tool['alt'], $tool['url'], $tool['width'], $tool['height'], $tool['top'], $tool['left'], $tool['onClose'], $tool['title'], $tool['footer']);
						break;
					case 'close':
						$bar->appendButton('Standard', 'cancel', JText::_('HIKA_CLOSE'), 'cancel', false, false);
						break;
					case 'hikacancel':
						$cancel_url = JRequest::getVar('cancel_redirect');
						if(!empty($cancel_url) || !empty($tool['url'])) {
							if(!empty($cancel_url)){
								$cancel_url = base64_decode($cancel_url);
								if(!hikashop_disallowUrlRedirect($cancel_url)){
									$bar->appendButton('Link', 'cancel', JText::_('HIKA_CANCEL'), $cancel_url);
								}
							}else
								$bar->appendButton('Link', 'cancel', JText::_('HIKA_CANCEL'), $tool['url'] );
						} else {
							$bar->appendButton('Standard', 'cancel', JText::_('HIKA_CANCEL'), 'cancel', false, false);
						}
						break;
					case 'pophelp':
						if(!empty($tool['target']))
							$bar->appendButton('Pophelp', $tool['target']);
						break;
					case 'export':
						$bar->appendButton('Export');
						break;
					case 'dashboard':
						if(hikashop_isAllowed($config->get('acl_dashboard_view','all')))
							$bar->appendButton('Link', 'hikashop', JText::_('HIKASHOP_CPANEL'), hikashop_completeLink('dashboard'));
						break;
				}
			}
		}
	}

	function customTool(&$bar, $toolname, $tool) {
		return false;
	}
}
