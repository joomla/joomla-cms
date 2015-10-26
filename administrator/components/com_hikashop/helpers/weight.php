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
class hikashopWeightHelper{
	var $conversion = array(
		'mg'=>array('g'=>0.001,'dag'=>0.0001,'kg'=>0.000001,'t'=>0.000000001,'lb'=>0.00000220462234,'oz'=>0.000035273962,'ozt'=>0.0000321507466),
		'g'=>array('t'=>0.000001,'kg'=>0.001,'mg'=>1000,'dag'=>0.1,'lb'=>0.00220462234,'oz'=>0.035273962,'ozt'=>0.0321507466),
		'dag'=>array('g'=>10,'t'=>0.00001,'kg'=>0.01,'mg'=>10000,'lb'=>0.0220462234,'oz'=>0.35273962,'ozt'=>0.321507466),
		'kg'=>array('t'=>0.001,'g'=>1000,'dag'=>100,'mg'=>1000000,'lb'=>2.20462234,'oz'=>35.273962,'ozt'=>32.1507466),
		't' =>array('kg'=>1000,'g'=>1000000,'dag'=>100000,'mg'=>100000000,'lb'=>2204.62234,'oz'=>35273.962,'ozt'=>32150.7466),
		'lb'=>array('t'=>0.0045359237,'kg'=>0.45359237,'dag'=>45.359237,'g'=>453.59237,'mg'=>453592.37,'oz'=>16,'ozt'=>14.5833333),
		'oz'=>array('t'=>0.0000028349523125,'kg'=>0.028349523125,'dag'=>2.8349523125,'g'=>28.349523125,'mg'=>28349.523125,'lb'=>0.0625,'ozt'=>0.911458333),
		'ozt'=>array('t'=>0.0000311034768,'kg'=>0.0311034768,'dag'=>3.11034768,'g'=>31.1034768,'mg'=>31103.4768,'lb'=>0.0685714286,'oz'=>1.09714286)
	);

	function __construct(){
		$this->getSymbol();
	}

	function convert($weight,$symbol_used='',$target=''){
		if(empty($target)){
			$target=$this->main_symbol;
		}
		if(empty($symbol_used)){
			$symbol_used=$this->main_symbol;
		}
		switch($target){
			case 'l':
				$target = 'kg';
				break;
			case 'cl':
				$target = 'dag';
				break;
			case 'ml':
				$target = 'g';
				break;
			default:
				break;
		}
		switch($symbol_used){
			case 'l':
				$symbol_used = 'kg';
				break;
			case 'cl':
				$symbol_used = 'dag';
				break;
			case 'ml':
				$symbol_used = 'g';
				break;
			default:
				break;
		}
		if($symbol_used != $target){
			$convert = $this->conversion[$symbol_used][$target];
			return $weight*$convert;
		}
		return $weight;
	}

	function getSymbol(){
		if(empty($this->main_symbol)){
			$config =& hikashop_config();
			$this->symbols = explode(',',$config->get('weight_symbols','kg,dag,g,mg,lb,oz,ozt'));
			foreach($this->symbols as $k => $symbol){
				$this->symbols[$k] = trim($symbol);
			}
			$this->main_symbol = array_shift($this->symbols);
		}
		return $this->main_symbol;
	}

}
