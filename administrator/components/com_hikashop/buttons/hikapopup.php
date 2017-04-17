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
if(!HIKASHOP_J30) {
	if(!HIKASHOP_PHP5){
		$toolbarInstance =& JToolbar::getInstance();
	} else {
		$toolbarInstance = JToolbar::getInstance();
	}
	$toolbarInstance->loadButtonType('Popup');
	class JButtonHikaPopup extends JButtonPopup {
		public function fetchButton($type = 'Popup', $name = '', $text = '', $url = '', $width = 640, $height = 480, $top = 0, $left = 0, $onClose = '', $title = '', $footer = '') {
			if(empty($title) && empty($footer))
				return parent::fetchButton($type, $name, $text, $url, $width, $height, $top, $left, $onClose);

			JHtml::_('behavior.modal');

			$text = JText::_($text);
			$class = $this->fetchIconClass($name);
			$doTask = $url; //$this->_getCommand($name, $url, $width, $height, $top, $left);
			$id = 'modal-toolbar-' . $name;

			$popup = hikaserial::get('shop.helper.popup');
			$params = array(
				'width' => $width,
				'height' => $height,
				'type' => 'link',
				'footer' => $footer
			);

			$html = $popup->displayMootools('<span class="'.$class.'"></span>'.$text, $title, $doTask, $id, $params);

			return $html;
		}
	}
} else {
	JToolbar::getInstance()->loadButtonType('Popup');
	class JToolbarButtonHikapopup extends JToolbarButtonPopup {
		public function fetchButton($type = 'Modal', $name = '', $text = '', $url = '', $width = 640, $height = 480, $top = 0, $left = 0, $onClose = '', $title = '') {
			list($name, $icon) = explode('#', $name, 2);
			$name .= '-btnpopup';
			if(empty($title))
				$title = $text;

			$options = array(
				'name' => JText::_($name),
				'text' => JText::_($text),
				'title' => JText::_($title),
				'class' => $this->fetchIconClass($name),
				'doTask' => $url // $this->_getCommand($url)
			);

			$id = 'modal-' . $name;

			if(!empty($footer)) {
				$footer = '<div class="modal-footer">'.
						'<button class="btn" type="button" data-dismiss="modal">'.JText::_('HIKA_CANCEL').'</button>'.
						'<button class="btn btn-primary" type="submit" onclick="window.hikashop.submitPopup(\\\''.$id.'\\\');">'.JText::_('HIKA_VALIDATE').'</button>'.
					'</div>';
			} else {
				$footer = '';
			}

			$params = array(
				'title' => $options['title'],
				'url' => $options['doTask'],
				'height' => $height,
				'width' => $width
			);

			$html = array(
				'<button onclick="'.$url.'" class="btn btn-small modal" data-toggle="modal" data-target="#modal-'.$name.'"><i class="icon-'.$icon.'"></i>'.JText::_($text).'</button>',
				'</div><div class="btn-group" style="width: 0; margin: 0">',
				str_replace(
					array(
						'id="'.$id.'"',
						'<iframe'
					),
					array(
						'id="'.$id.'" style="width:'.($params['width']+20).'px;height:'.($params['height']+90).'px;margin-left:-'.(($params['width']+20)/2).'px"',
						'<iframe id="'.$id.'-iframe"'
					),
					JHtml::_('bootstrap.renderModal', 'modal-' . $name, $params, $footer)
				)
			);

			if(strlen($onClose) >= 1) {
				$html[] = '<script>' . "\n"
					. 'jQuery(document).ready(function(){jQuery("#modal-'.$name.'").appendTo(jQuery(document.body));});' . "\n"
					. 'jQuery(\'#modal-' . $name . '\').on(\'hide\', function () {' . $onClose . ';});' . "\n"
					. '</script>';
			} else {
				$html[] = '<script>' . "\n"
					. 'jQuery(document).ready(function(){jQuery("#modal-'.$name.'").appendTo(jQuery(document.body));});' . "\n"
					. '</script>';
			}

			return implode("\n", $html);
		}
	}
}
