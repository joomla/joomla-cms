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
class hikashopTabsHelper {
	var $ctrl = 'tabs';
	var $tabs = null;
	var $openPanel = false;
	var $mode = null;
	var $data = array();
	var $options = null;
	var $name = '';

	function __construct() {
		if(!HIKASHOP_J16) {
			$this->mode = 'pane';
		} elseif(!HIKASHOP_J30) {
			$this->mode = 'tabs';
		} else {
			$app = JFactory::getApplication();
			if(($app->isAdmin() && HIKASHOP_BACK_RESPONSIVE) || (!$app->isAdmin() && HIKASHOP_RESPONSIVE)){
				$this->mode = 'bootstrap';
			}else{
				$this->mode = 'tabs';
			}
		}
	}

	function startPane($name) { return $this->start($name); }
	function startPanel($text, $id) { return $this->panel($text, $id); }
	function endPanel() { return ''; }
	function endPane() { return $this->end(); }

	function setOptions($options = array()) {
		if($this->options == null)
			$this->options = $options;
		else
			$this->options = array_merge($this->options, $options);
	}

	function start($name, $options = array()) {
		$ret = '';
		if($this->mode == 'pane') {
			jimport('joomla.html.pane');
			if(!empty($this->options))
				$options = array_merge($options, $this->options);
			$this->tabs = JPane::getInstance('tabs', $options);
			$ret .= $this->tabs->startPane($name);
		} elseif($this->mode == 'tabs') {
			if(!empty($this->options))
				$options = array_merge($options, $this->options);
			$ret .= JHtml::_('tabs.start', $name, $options);
		} else {
			$this->name = $name;
			if($this->options == null)
				$this->options = $options;
			else
				$this->options = array_merge($this->options, $options);
		}
		return $ret;
	}

	function panel($text, $id) {
		$ret = '';
		if($this->mode == 'pane') {
			if($this->openPanel)
				$ret .= $this->tabs->endPanel();
			$ret .= $this->tabs->startPanel($text, $id);
			$this->openPanel = true;
		} elseif($this->mode == 'tabs') {
			$ret .= JHtml::_('tabs.panel', JText::_($text), $id);
		} else {
			if($this->openPanel)
				$this->_closePanel();

			$obj = new stdClass();
			$obj->text = $text;
			$obj->id = $id;
			$obj->data = '';
			$this->data[] = $obj;
			ob_start();
			$this->openPanel = true;
		}
		return $ret;
	}

	function _closePanel() {
		if(!$this->openPanel)
			return;
		$panel = end($this->data);
		$panel->data .= ob_get_clean();
		$this->openPanel = false;
	}

	function end() {
		$ret = '';
		if($this->mode == 'pane') {
			if($this->openPanel)
				$ret .= $this->tabs->endPanel();
			$ret .= $this->tabs->endPane();
		} elseif($this->mode == 'tabs') {
			$ret .= JHtml::_('tabs.end');
		} else {
			static $jsInit = false;

			if($this->openPanel)
				$this->_closePanel();

			$classes = '';
			if(isset($this->options['useCookie']) && $this->options['useCookie']) {
				$classes .= ' nav-remember';
			}

			$ret .= '<div><ul class="nav nav-tabs'.$classes.'" id="'.$this->name.'" style="width:100%;">'."\r\n";
			foreach($this->data as $k => $data) {
				$active = '';
				if((isset($this->options['startOffset']) && $this->options['startOffset'] == $k) || $k == 0)
					$active = ' class="active"';
				$ret .= '	<li' . $active.'><a href="#' . $data->id . '" id="'.$data->id.'_tablink" data-toggle="tab">' . JText::_($data->text) . '</a></li>'."\r\n";
			}
			$ret .= '</ul>'."\r\n".'<div class="tab-content">'."\r\n";
			foreach($this->data as $k => $data) {
				$active = '';
				if((isset($this->options['startOffset']) && $this->options['startOffset'] == $k) || $k == 0)
					$active = ' active';
				$ret .= '	<div class="tab-pane' . $active.'" id="' . $data->id . '">'."\r\n".$data->data."\r\n".'	</div>'."\r\n";
				unset($data->data);
			}
			$ret .= '</div></div>';
			unset($this->data);

			if(!$jsInit) {
				$jsInit = true;
				$js = 'jQuery(document).ready(function (){
	jQuery("ul.nav-remember").each(function(nav){
		var id = jQuery(this).attr("id");
		jQuery("#" + id + " a[data-toggle=\"tab\"]").on("shown", function (e) {
			if(localStorage) {
				localStorage.setItem("hikashop-lastTab-"+id, jQuery(e.target).attr("id"));
			} else {
				var expire = new Date(); expire.setDate(expire.getDate() + 5);
				document.cookie = "hikashop-lastTab-"+id+"="+escape(jQuery(e.target).attr("id"))+"; expires="+expire;
			}
		});
		var lastTab = null;
		if(localStorage) {
			lastTab = localStorage.getItem("hikashop-lastTab-"+id);
		} else {
			if(document.cookie.length > 0 && document.cookie.indexOf("hikashop-lastTab-"+id+"=") != -1) {
				var s = "hikashop-lastTab-"+id+"=", o = document.cookie.indexOf(s) + s.length, e = document.cookie.indexOf(";",o);
				if(e == -1) e = document.cookie.length;
				lastTab = unescape(document.cookie.substring(o, e));
			}
		}
		if (lastTab) {
			jQuery("#"+lastTab).tab("show");
		}
	});
});';
				$doc = JFactory::getDocument();
				$doc->addScriptDeclaration($js);
			}
		}
		return $ret;
	}
}
