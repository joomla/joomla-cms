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
	if(!isset($this->reload[$this->params->table][$this->params->column]) && isset($this->params->data_id)){
		$massaction = hikashop_get('class.massaction');
		$table_id = $this->params->table.'_id';
		foreach($this->params->ids as $id){
			foreach($this->params->elements as $element){
				if(isset($element->$table_id) && $element->$table_id == $id){
					echo $massaction->displayByType($this->params->types,$element,$this->params->column).'<br/>';
				}elseif(!empty($element)){
					foreach($element as $elem){
						if(!is_array($elem)){continue;}
						foreach($elem as $data){
							if(count($this->params->ids) < 2 || (isset($data->$table_id) && $data->$table_id == $id)){
								echo $massaction->displayByType($this->params->types,$data,$this->params->column).'<br/>';
							}
						}
					}
				}
			}
		}
	}else if(!isset($this->params->data_id)){
		echo $this->params->column;
	}else{
		echo JText::_( 'LOADING' );
	}
?>
<?php echo JHTML::_( 'form.token' ); ?>
<?php exit;
