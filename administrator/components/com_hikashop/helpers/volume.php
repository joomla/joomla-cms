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
class hikashopVolumeHelper{
	var $conversion = array(
		'm'=>array('dm'=>1000,'cm'=>1000000,'mm'=>1000000000,'ft'=>35.31466672,'in'=>61023.74409473,'yd'=>1.30795062),
		'dm'=>array('m'=>0.001,'cm'=>1000,'mm'=>1000000,'ft'=>0.03531466672,'in'=>61.02374409473,'yd'=>0.00130795062),
		'cm'=>array('m'=>0.000001,'dm'=>0.001,'mm'=>1000,'ft'=>0.00003531,'in'=>0.06102374,'yd'=>0.00000131),
		'mm'=>array('m'=>0.000000001,'dm'=>0.000001,'cm'=>0.001,'ft'=>0.000000035312274,'in'=>0.00006102,'yd'=>0.0000000013078621),
		'in'=>array('m'=>0.00001639,'dm'=>0.016387064,'cm'=>16.387064,'mm'=>16387.064,'ft'=>0.0005787,'yd'=>0.00002143),
		'ft'=>array('m'=>0.02831685,'dm'=>28.316846592,'cm'=>28316.846592,'mm'=>28316846.592,'in'=>1728,'yd'=>0.03703704),
		'yd'=>array('m'=>0.76455486,'dm'=>764.554857984,'cm'=>764554.857984,'mm'=>764554857.9839998,'in'=>46656,'ft'=>27),
	);
	var $conversionDimension = array(
		'm'=>array('dm'=>10,'cm'=>100,'mm'=>1000,'ft'=>3.2808399,'in'=>39.3700787,'yd'=>1.0936133),
		'dm'=>array('m'=>0.1,'cm'=>10,'mm'=>100,'ft'=>0.32808399,'in'=>3.93700787,'yd'=>0.10936133),
		'cm'=>array('m'=>0.01,'dm'=>0.1,'mm'=>10,'ft'=>0.032808399,'in'=>0.393700787,'yd'=>0.010936133),
		'mm'=>array('m'=>0.001,'dm'=>0.01,'cm'=>0.1,'ft'=>0.0032808399,'in'=>0.0393700787,'yd'=>0.0010936133),
		'in'=>array('m'=> 0.0254,'dm'=> 0.254,'cm'=> 2.54,'mm'=>25.4,'ft'=>0.0833333333,'yd'=>0.0277777778),
		'ft'=>array('m'=>0.3048,'dm'=>3.048,'cm'=>30.48,'mm'=>304.8,'in'=>12,'yd'=>0.333333333),
		'yd'=>array('m'=>0.9144,'dm'=>9.144,'cm'=>91.44,'mm'=>914.4,'in'=>36,'ft'=>3),
	);

	function hikashopVolumeHelper(){
		$this->getSymbol();
	}

	function convert($weight,$symbol_used='',$target='', $mode='volume'){
		if(empty($target)){
			$target=$this->main_symbol;
		}
		if(empty($symbol_used)){
			$symbol_used=$this->main_symbol;
		}

		if($symbol_used != $target){
			if($mode=='volume'){
				$convert = $this->conversion[$symbol_used][$target];
				return $weight*$convert;
			}
			if($mode=='dimension'){
				$convert = $this->conversionDimension[$symbol_used][$target];
				return $weight*$convert;
			}
		}
		return $weight;
	}

	function getSymbol(){
		if(empty($this->main_symbol)){
			$config =& hikashop_config();
			$this->symbols = explode(',',$config->get('volume_symbols','m,dm,cm,mm,in,ft,yd'));
			foreach($this->symbols as $k => $symbol){
				$this->symbols[$k] = trim($symbol);
			}
			$this->main_symbol = array_shift($this->symbols);
		}
		return $this->main_symbol;
	}

}
