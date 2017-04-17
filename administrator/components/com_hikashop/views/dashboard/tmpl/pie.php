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
$i=0;
foreach($this->widget->elements as $element){
	$data[] = 'data.setValue('.$i.', 0, \''.str_replace("'","\'",$element->name).'\');
				data.setValue('.$i.', 1, '.(int)$element->total.');';
	$i++;
}

if(isset($this->edit) && $this->edit){
	$height='600';
	$width='600';
	$legend='';
}else{
	$height='210';
	$width='300';
	$legend=", legend: 'none'";
}

$js="
google.load('visualization', '1', {'packages':['corechart']});
			google.setOnLoadCallback(drawChart_".$this->widget->widget_id.");
			function drawChart_".$this->widget->widget_id."() {
				var data = new google.visualization.DataTable();
				data.addColumn('string', 'name');
				data.addColumn('number', 'total');
				data.addRows(".count($data).");
				".implode("\n",$data)."

				var chart = new google.visualization.PieChart(document.getElementById('graph_".$this->widget->widget_id."'));
				chart.draw(data, {width: ".$width.", height: ".$height." ".$legend."});
			}";
if (!HIKASHOP_PHP5) {
	$doc =& JFactory::getDocument();
}else{
	$doc = JFactory::getDocument();
}
$doc->addScriptDeclaration($js);

?>
<div id="graph_<?php echo $this->widget->widget_id; ?>" style="height:<?php echo $height; ?>px;" align="center"></div>
