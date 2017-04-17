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
$data = array();
$types=array();
$values='';
foreach($this->widget->elements as $element){
	if(isset($element->type) && !isset($types[$element->type])){
		$types[$element->type]="data.addColumn('number', '".$element->type."')";
	}
}
if(empty($types)){
	$types[]="data.addColumn(data.addColumn('number', undefined))";
}

foreach($this->widget->elements as $element){
	if(isset($element->type)){
		$values=array();
		foreach($types as $type => $k){
			if ($type==$element->type){
				$values[]=(int)$element->total;
			}else{
				$values[]='null';
			}
		}
		$values=implode(', ',$values);
	}else{
		$values=(int)$element->total;
	}
	$data[] = '[new Date('.$element->year.', '.(int)$element->month.', '.(int)$element->day.', '.@(int)$element->hour.'), '.$values.']';
}
if(!isset($this->widget->widget_id)){
	$id='preview';
}else{
	$id=$this->widget->widget_id;
}
$js="
google.load('visualization', '1', {'packages':['annotatedtimeline']});
			google.setOnLoadCallback(drawChart_".$id.");
			function drawChart_".$id."() {
				var data = new google.visualization.DataTable();
				data.addColumn('date', undefined);
				 ".implode('; ', $types)."
				data.addRows([
					".implode(', ',$data)."
				]);
		var el = document.getElementById('graph_".$id."');
				var chart = new google.visualization.AnnotatedTimeLine(el);
				chart.draw(data,{'wmode':'transparent'});
				el.style.width = null;
			}";
if (!HIKASHOP_PHP5) {
	$doc =& JFactory::getDocument();
}else{
	$doc = JFactory::getDocument();
}
$doc->addScriptDeclaration($js);
if(isset($this->edit) && $this->edit){
	$size='width: 900px; height: 500px;';
}else{
	$size='width: 300px; height: 210px;';
}
?>
<div id="graph_<?php echo $id; ?>" style="<?php echo $size; ?>" align="center"></div>
