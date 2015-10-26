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

class JHtmlHikaselect extends JHTMLSelect {
	static $event = false;

	public static function booleanlist($name, $attribs = null, $selected = null, $yes = 'JYES', $no = 'JNO', $id = false){
		$arr = array(
			JHtml::_('select.option', '1', JText::_($yes)),
			JHtml::_('select.option', '0', JText::_($no))
		);
		$arr[0]->booleanlist = true;
		$arr[0]->class = 'btn-success';

		$arr[1]->booleanlist = true;
		$arr[1]->class = 'btn-danger';

		return JHtml::_('hikaselect.radiolist', $arr, $name, $attribs, 'value', 'text', (int) $selected, $id);
	}

	public static function radiolist($data, $name, $attribs = null, $optKey = 'value', $optText = 'text', $selected = null, $idtag = false, $translate = false, $vertical = false, $default = ''){
		reset($data);
		$app = JFactory::getApplication();

		if(!self::$event) {
			self::$event = true;
			$doc = JFactory::getDocument();

			JHtml::_('jquery.framework');
			$doc->addScriptDeclaration('
(function($){
if(!window.hikashopLocal)
	window.hikashopLocal = {};
window.hikashopLocal.radioEvent = function(el) {
	var id = $(el).attr("id"), c = $(el).attr("class"), lbl = $("label[for=\"" + id + "\"]"),
		v = $(el).val(), target = $(el).parent().find("label[data-default=\"1\"]");
	if(v == "-1")
		target.addClass("btn-default");
	else
		target.removeClass("btn-default");
	if(c !== undefined && c.length > 0)
		lbl.addClass(c);
	lbl.addClass("active");
	$("input[name=\"" + $(el).attr("name") + "\"]").each(function() {
		if($(this).attr("id") != id) {
			c = $(this).attr("class");
			lbl = $("label[for=\"" + jQuery(this).attr("id") + "\"]");
			if(c !== undefined && c.length > 0)
				lbl.removeClass(c);
			lbl.removeClass("active");
		}
	});
}
$(document).ready(function() {
	setTimeout(function(){ $(".hikaradios .btn-group label").off("click"); }, 200);
});
})(jQuery);');
		}

		if($app->isAdmin()) {
			$yes_text = JText::_('JYES');
			$no_text = JText::_('JNO');
			foreach($data as &$obj) {
				if(!empty($obj->class))
					continue;

				$obj->class = 'btn-primary';
				if(($translate && $obj->$optText == 'JYES') || (!$translate && $obj->$optText == $yes_text))
					$obj->class = 'btn-success';
				if(($translate && $obj->$optText == 'JNO') || (!$translate && $obj->$optText == $no_text))
					$obj->class = 'btn-danger';
			}
			unset($obj);
		}

		if (is_array($attribs))	{
			$attribs = JArrayHelper::toString($attribs);
		}

		$id_text = str_replace(array('[',']'),array('_',''),$idtag ? $idtag : $name);

		$backend = false && $app->isAdmin();
		$htmlLabels = '';
		$html = '<div class="hikaradios" id="'.$id_text.'">';

		foreach ($data as $obj) {
			$k = $obj->$optKey;
			$t = $translate ? JText::_($obj->$optText) : $obj->$optText;
			$class = isset($obj->class) ? $obj->class : '';
			$sel = false;
			$extra = $attribs;
			$currId = $id_text . $k;
			if(isset($obj->id))
				$currId = $obj->id;

			if (is_array($selected)) {
				foreach ($selected as $val) {
					$k2 = is_object($val) ? $val->$optKey : $val;
					if ($k == $k2) {
						$extra .= ' selected="selected"';
						$sel = true;
						break;
					}
				}
			} elseif((string) $k == (string) $selected) {
				$extra .= ' checked="checked"';
				$sel = true;
			}

			$extra = ' '.$extra;
			if(strpos($extra, ' style="') !== false) {
				$extra = str_replace(' style="', ' style="display:none;', $extra);
			} elseif(strpos($extra, 'style=\'') !== false) {
				$extra = str_replace(' style=\'', ' style=\'display:none;', $extra);
			} else {
				$extra .= ' style="display:none;"';
			}
			if(strpos($extra, ' onchange="') !== false) {
				$extra = str_replace(' onchange="', ' onchange="hikashopLocal.radioEvent(this);', $extra);
			} elseif(strpos($extra, 'onchange=\'') !== false) {
				$extra = str_replace(' onchange=\'', ' onchange=\'hikashopLocal.radioEvent(this);', $extra);
			} else {
				$extra .= ' onchange="hikashopLocal.radioEvent(this);"';
			}
			if(!empty($obj->class)) {
				if(strpos($extra, 'class="') === false)
					$extra .= ' class="'.$obj->class.'"';
				else
					$extra = str_replace('class="', 'class="'.$obj->class.' ', $extra);
			}
			$html .= "\n\t" . '<input type="radio" name="' . $name . '"' . ' id="' . $currId . '" value="' . $k . '"' . ' ' . trim($extra) . '/>';

			$dataDefault = '0';
			$addClass = '';
			if(isset($obj->default) && $obj->default){
				$dataDefault = '1';
				$addClass = 'btn-default-lbl';
				if($selected == '-1')
					$addClass .= ' btn-default';
			}

			$htmlLabels .= "\n\t"."\n\t" . '<label for="' . $currId . '"' . ' data-default="'.$dataDefault.'" class="btn '.$addClass.($sel ? ' active '.$class : '') .'">' . $t . '</label>';
		}

		$html .= "\r\n" . '<div class="btn-group'. ($vertical?' btn-group-vertical':'').'" data-toggle="buttons-radio">' . $htmlLabels . "\r\n" . '</div>';
		$html .= "\r\n" . '</div>' . "\r\n";
		return $html;
	}

	public static function inheritRadiolist($name, $selected = null, $extra = array(), $attribs = null, $id = false){
		$arr = array(
			JHtml::_('select.option', '-1', JText::_('HIKA_INHERIT')),
			JHtml::_('select.option', '1', JText::_('JYES')),
			JHtml::_('select.option', '0', JText::_('JNO'))
		);

		if(!is_array($extra))
			$extra = array($extra);
		foreach($extra as $option){
			$arr[] = $option;
		}

		$shortName = str_replace(']','',preg_replace('#(.*)\[#','',$name));
		$config = hikashop_config();

		if($shortName == 'display_filters')
			$shortName = 'show_filters';

		$default = $config->get($shortName,'');
		$default_params = $config->get('default_params');
		if(isset($default_params[$shortName])){
			$default = $default_params[$shortName];
		}
		foreach($arr as $k => $v){
			$arr[$k]->booleanlist = true;
			if($v->value == $default)
				$v->default = true;
		}
		return self::radiolist($arr, $name, $attribs, 'value', 'text', (int) $selected, $id);
	}

}
