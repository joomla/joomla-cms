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
class JButtonPophelp extends JButton {
	private $name = 'Pophelp';

	public function fetchButton($type = 'Pophelp', $namekey = '', $id = 'pophelp') {
		$doc = JFactory::getDocument();
		$config = hikashop_config();
		$level = $config->get('level');
		$url = HIKASHOP_HELPURL . $namekey . '&level=' . $level;
		if(hikashop_isSSL())
			$url = str_replace('http://', 'https://', $url);

		$js = '
function displayDoc(){
	var d = document, init = false, b = d.getElementById("iframedoc");
	if(!b) return true;
	if(typeof(b.openHelp) == "undefined") { b.openHelp = true; init = true; }
	if(b.openHelp) { b.innerHTML = \'<iframe src="'.$url.'" width="100%" height="100%" style="border:0px" border="no" scrolling="auto"></iframe>\'; b.setStyle("display","block"); }
	try {
		if(typeof(b.fxEffect) == "undefined") { b.fxEffect = b.effects({duration: 1500, transition: Fx.Transitions.Quart.easeOut}); }
		if(b.openHelp){
			if(init) { b.height = 0; b.style.height = 0; }
			b.fxEffect.stop(); b.fxEffect.start({height: 300});
		}else{
			b.fxEffect.stop(); b.fxEffect.start({height: 0}).chain(function() { b.innerHTML = ""; b.setStyle("display", "none"); });
		}
	} catch(err) {
		if(typeof(b.vslide) == "undefined") { b.vslide = new Fx.Slide("iframedoc"); }
		if(b.openHelp){
			if(init) { b.vslide.hide(); }
			b.vslide.slideIn();
		}else{
			b.vslide.slideOut().chain(function() { b.innerHTML = ""; b.setStyle("display", "none");	});
		}
	}
	b.openHelp = !b.openHelp;
	return false;
}';
		$doc->addScriptDeclaration($js);
		if(!HIKASHOP_J30)
			return '<a href="' . $url . '" target="_blank" onclick="return displayDoc();" class="toolbar"><span class="icon-32-help" title="' . JText::_('HIKA_HELP', true) . '"></span>' . JText::_('HIKA_HELP') . '</a>';
		return '<button class="btn btn-small" onclick="return displayDoc();"><i class="icon-help"></i> '.JText::_('HIKA_HELP').'</button>';
	}

	public function fetchId($type = 'Pophelp', $html = '', $id = 'pophelp') {
		return $this->name . '-' . $id;
	}
}

class JToolbarButtonPophelp extends JButtonPophelp {}
