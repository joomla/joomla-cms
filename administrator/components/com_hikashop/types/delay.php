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
class hikashopDelayType{
	var $values = array();
	var $num = 0;
	var $onChange = '';
	function hikashopDelayType(){
		static $i = 0;
		$i++;
		$this->num = $i;
		$js = "function updateDelay".$this->num."(){";
			$js .= "delayvar = window.document.getElementById('delayvar".$this->num."');";
			$js .= "delaytype = window.document.getElementById('delaytype".$this->num."').value;";
			$js .= "delayvalue = window.document.getElementById('delayvalue".$this->num."');";
			$js .= "realValue = delayvalue.value;";
			$js .= "if(delaytype == 'minute'){realValue = realValue*60; }";
			$js .= "if(delaytype == 'hour'){realValue = realValue*3600; }";
			$js .= "if(delaytype == 'day'){realValue = realValue*86400; }";
			$js .= "if(delaytype == 'week'){realValue = realValue*604800; }";
			$js .= "if(delaytype == 'month'){realValue = realValue*2592000; }";
			$js .= "if(delaytype == 'year'){realValue = realValue*31556926; }";
			$js .= "delayvar.value = realValue;";
		$js .= '}';
		if (!HIKASHOP_PHP5) {
			$doc =& JFactory::getDocument();
		}else{
			$doc = JFactory::getDocument();
		}
		$doc->addScriptDeclaration( $js );
	}
	function display($map,$value,$type = 1){
		$this->values=array();
		if($type == 0){
			$this->values[] = JHTML::_('select.option', 'second',JText::_('HIKA_SECONDS'));
			$this->values[] = JHTML::_('select.option', 'minute',JText::_('HIKA_MINUTES'));
		}elseif($type == 1){
			$this->values[] = JHTML::_('select.option', 'minute',JText::_('HIKA_MINUTES'));
			$this->values[] = JHTML::_('select.option', 'hour',JText::_('HOURS'));
			$this->values[] = JHTML::_('select.option', 'day',JText::_('DAYS'));
			$this->values[] = JHTML::_('select.option', 'week',JText::_('WEEKS'));
		}elseif($type == 2){
			$this->values[] = JHTML::_('select.option', 'minute',JText::_('HIKA_MINUTES'));
			$this->values[] = JHTML::_('select.option', 'hour',JText::_('HOURS'));
		}elseif($type == 3){
			$this->values[] = JHTML::_('select.option', 'hour',JText::_('HOURS'));
			$this->values[] = JHTML::_('select.option', 'day',JText::_('DAYS'));
			$this->values[] = JHTML::_('select.option', 'week',JText::_('WEEKS'));
			$this->values[] = JHTML::_('select.option', 'month',JText::_('MONTHS'));
		}elseif($type == 4){
			$this->values[] = JHTML::_('select.option', 'day',JText::_('DAYS'));
			$this->values[] = JHTML::_('select.option', 'week',JText::_('WEEKS'));
			$this->values[] = JHTML::_('select.option', 'month',JText::_('MONTHS'));
			$this->values[] = JHTML::_('select.option', 'year',JText::_('YEARS'));
		}
		$return = $this->get($value,$type);
		$delayValue = '<input class="inputbox" onchange="updateDelay'.$this->num.'();'.$this->onChange.'" type="text" name="delayvalue'.$this->num.'" id="delayvalue'.$this->num.'" size="10" value="'.$return->value.'" /> ';
		$delayVar = '<input type="hidden" name="'.$map.'" id="delayvar'.$this->num.'" value="'.$value.'"/>';
		return $delayValue.JHTML::_('select.genericlist',   $this->values, 'delaytype'.$this->num, 'class="inputbox" size="1" onchange="updateDelay'.$this->num.'();'.$this->onChange.'"', 'value', 'text', $return->type ,'delaytype'.$this->num).$delayVar;
	}
	function get($value,$type){
		$return = new stdClass();
		$return->value = $value;
		if($value%31556926 == 0){
			$return->type = 'year';
			$return->value = (int) $value / 31556926;
			return $return;
		}
		if($type == 0){
			$return->type = 'second';
		}else{
			$return->type = 'minute';
		}
		if($return->value >= 60  AND $return->value%60 == 0){
			$return->value = (int) $return->value / 60;
			$return->type = 'minute';
			if($type != 0 AND $return->value >=60 AND $return->value%60 == 0){
				$return->type = 'hour';
				$return->value = $return->value / 60;
				if($type != 2 AND $return->value >=24 AND $return->value%24 == 0){
					$return->type = 'day';
					$return->value = $return->value / 24;
					if($type == 3 AND $return->value >=30 AND $return->value%30 == 0){
						$return->type = 'month';
						$return->value = $return->value / 30;
					}elseif($return->value >=7 AND $return->value%7 == 0){
						$return->type = 'week';
						$return->value = $return->value / 7;
					}
				}
			}
		}
		return $return;
	}

	function displayDelay($value){
		if(empty($value)) return 0;
		$type = 'HIKA_SECONDS';
		if($value >= 60  AND $value%60 == 0){
			$value = (int) $value / 60;
			$type = 'HIKA_MINUTES';
			if($value >=60 AND $value%60 == 0){
				$type = 'HOURS';
				$value = $value/ 60;
				if($value >=24 AND $value%24 == 0){
					$type = 'DAYS';
					$value = $value / 24;
					if($value >= 30 AND $value%30 == 0){
						$type = 'MONTHS';
						$value = $value / 30;
					}elseif($value >=7 AND $value%7 == 0){
						$type = 'WEEKS';
						$value = $value / 7;
					}
				}
			}
		}
		return $value.' '.JText::_($type);
	}
	function displayDelaySECtoDAY($value,$type){
		if ( $type == 0 ){
			$value = (int) $value / 60;
			$value = round($value);
		}
		if ( $type == 1 ){
			$value = (int) $value / 3600;
			$value = round($value);
		}
		if ( $type == 2 ){
			$value = (int) $value / 86400;
			$value = round($value);
		}
		return $value;
	}
}
