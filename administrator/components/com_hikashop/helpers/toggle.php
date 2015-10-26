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
class hikashopToggleHelper{
	var $ctrl = 'toggle';
	var $extra = '';
	var $token = '';

	function __construct(){
		$this->token = '&'.hikashop_getFormToken().'=1';
	}

	function _getToggle($column,$table = '') {
		$params = new stdClass();
		$params->mode = 'pictures';
		if(!HIKASHOP_J16){
			$params->pictures = array(0=>'images/publish_x.png',1=>'images/tick.png',-2=>'images/publish_x.png');
		}elseif(!HIKASHOP_J30){
			$params->aclass = array(0=>'grid_false',1=>'grid_true',-2=>'grid_false');
		} else {
			$params->aclass = array(0=>'icon-unpublish',1=>'icon-publish',-2=>'icon-unpublish');
		}
		$params->values = array(0=>1,1=>0,-2=>1);
		return $params;
	}

	function toggle($id,$value,$table,$extra = null){
		$column = substr($id,0,strpos($id,'-'));
		$params = $this->_getToggle($column,$table);
		$newValue = $params->values[$value];
		if($params->mode == 'pictures'){
			static $pictureincluded = false;
			if(!$pictureincluded){
				$pictureincluded = true;
				$js = "function joomTogglePicture(id,newvalue,table){
					window.document.getElementById(id).className = 'onload';
					try{
						new Ajax('index.php?option=com_hikashop&tmpl=component&ctrl=".$this->ctrl.$this->token."&task='+id+'&value='+newvalue+'&table='+table,{ method: 'get', update: $(id), onComplete: function() {	window.document.getElementById(id).className = 'loading'; }}).request();
					}catch(err){
						new Request({url:'index.php?option=com_hikashop&tmpl=component&ctrl=".$this->ctrl.$this->token."&task='+id+'&value='+newvalue+'&table='+table,method: 'get', onComplete: function(response) { $(id).innerHTML = response; window.document.getElementById(id).className = 'loading'; }}).send();
					}
				}";
				if (!HIKASHOP_PHP5) {
					$doc =& JFactory::getDocument();
				}else{
					$doc = JFactory::getDocument();
				}
				$doc->addScriptDeclaration( $js );
			}
			$desc = empty($params->description[$value]) ? '' : $params->description[$value];
			if(empty($params->pictures)){
				$text = ' ';
				$class='class="'.$params->aclass[$value].'"';
			}else{
				$text = '<img src="'.$params->pictures[$value].'"/>';
				$class = '';
			}
			return '<a href="javascript:void(0);" '.$class.' onclick="joomTogglePicture(\''.$id.'\',\''.$newValue.'\',\''.$table.'\')" title="'.$desc.'">'.$text.'</a>';
		}elseif($params->mode == 'class'){
			static $classincluded = false;
			if(!$classincluded){
				$classincluded = true;
				$js = "function joomToggleClass(id,newvalue,table,extra){
					var mydiv=$(id); mydiv.innerHTML = ''; mydiv.className = 'onload';
					try{
						new Ajax('index.php?option=com_hikashop&tmpl=component&ctrl=".$this->ctrl.$this->token."&task='+id+'&value='+newvalue+'&table='+table+'&extra[color]='+extra,{ method: 'get', update: $(id), onComplete: function() {	window.document.getElementById(id).className = 'loading'; }}).request();
					}catch(err){
						new Request({url:'index.php?option=com_hikashop&tmpl=component&ctrl=".$this->ctrl.$this->token."&task='+id+'&value='+newvalue+'&table='+table+'&extra[color]='+extra,method: 'get', onComplete: function(response) { $(id).innerHTML = response; window.document.getElementById(id).className = 'loading'; }}).send();
					}
				}";
				if (!HIKASHOP_PHP5) {
					$doc =& JFactory::getDocument();
				}else{
					$doc = JFactory::getDocument();
				}
				$doc->addScriptDeclaration( $js );
			}
			$desc = empty($params->description[$value]) ? '' : $params->description[$value];
			$return = '<a class="btn btn-micro active" href="javascript:void(0);" onclick="joomToggleClass(\''.$id.'\',\''.$newValue.'\',\''.$table.'\',\''.urlencode($extra['color']).'\');" title="'.$desc.'"><div class="'. $params->class[$value] .'" style="background-color:'.$extra['color'].'">';
			if(!empty($extra['tooltip'])) $return .= JHTML::_('tooltip', $extra['tooltip'], '','','&nbsp;&nbsp;&nbsp;&nbsp;');
			$return .= '</div></a>';
			return $return;
		}
	}

	function display($column, $value) {
		$params = $this->_getToggle($column);
		if(empty($params->pictures)) {
			return '<div class="toggle_loading"><a class="'.$params->aclass[$value].'" href="#" onclick="return false;" style="cursor:default;"></a></div>';
		}
		return '<img src="'.$params->pictures[$value].'"/>';
	}

	function delete($lineId,$elementids,$table,$confirm = false,$text=''){
		$this->addDeleteJS();
		if(empty($text)) $text = '<img src="'.HIKASHOP_IMAGES.'delete.png"/>';
		return '<a href="javascript:void(0);" onclick="joomDelete(\''.$lineId.'\',\''.$elementids.'\',\''.$table.'\','. ($confirm ? 'true' : 'false').')">'.$text.'</a>';
	}

	function addDeleteJS(){
		static $deleteJS = false;
		if(!$deleteJS){
			$deleteJS = true;
			$js = "function joomDelete(lineid,elementids,table,reqconfirm){
				if(reqconfirm){
					if(!confirm('".JText::_('HIKA_VALIDDELETEITEMS',true)."')) return false;
				}

				try{
					new Ajax('index.php?option=com_hikashop&tmpl=component&ctrl=".$this->ctrl.$this->extra.$this->token."&task=delete&value='+elementids+'&table='+table, { method: 'get', onComplete: function() {window.document.getElementById(lineid).style.display = 'none';}}).request();
				}catch(err){
					new Request({url:'index.php?option=com_hikashop&tmpl=component&ctrl=".$this->ctrl.$this->extra.$this->token."&task=delete&value='+elementids+'&table='+table,method: 'get', onComplete: function() { window.document.getElementById(lineid).style.display = 'none'; }}).send();
				}
			}";
			if (!HIKASHOP_PHP5) {
				$doc =& JFactory::getDocument();
			}else{
				$doc = JFactory::getDocument();
			}
			$doc->addScriptDeclaration( $js );
		}
	}
}
