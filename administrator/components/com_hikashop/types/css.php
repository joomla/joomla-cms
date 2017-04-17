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
class hikashopCssType{
	var $type = 'component';
	function load(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '',JText::_('HIKA_NONE'));
		jimport('joomla.filesystem.folder');
		$regex = '^'.$this->type.'_([-_A-Za-z0-9]*)\.css$';
		$allCSSFiles = JFolder::files( HIKASHOP_MEDIA.'css', $regex );
		foreach($allCSSFiles as $oneFile){
			preg_match('#'.$regex.'#i',$oneFile,$results);
			$this->values[] = JHTML::_('select.option', $results[1],$results[1]);
		}
	}
	function display($map,$value){
		$this->load();
		if(count($this->values) == 1 && $this->type == 'style') {
			return hikashop_tooltip(JText::_('STYLE_TOOLTIP_TEXT'), JText::_('STYLE_TOOLTIP_TITLE'), '', JText::_('STYLE_HIKASHOP'), HIKASHOP_REDIRECT.'hikashop-styles');
		}
		$js = ' onchange="updateCSSLink(\''.$this->type.'\',\''.$this->type.'\',this.value);"';
		$aStyle = empty($value) ? ' style="display:none"' : '';
		$html = JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1"', 'value', 'text', $value,$this->type.'_choice' );
		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_config_manage','all'));
		if($manage){
			$popup = hikashop_get('helper.popup');
			$html .= $popup->display(
				'<img src="'. HIKASHOP_IMAGES.'edit.png" alt="'.JText::_('HIKA_EDIT').'"/>',
				'CSS',
				'\''.'index.php?option=com_hikashop&amp;tmpl=component&amp;ctrl=config&amp;task=css&amp;file='.$this->type.'_\'+document.getElementById(\''.$this->type.'_choice'.'\').value+\'&amp;var='.$this->type.'\'',
				$this->type.'_link',
				760,480, $aStyle, '', 'link',true
			);
		}
		return $html;
	}
}
