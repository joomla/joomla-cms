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
$js="
google.load('visualization', '1', {packages:['gauge']});
			google.setOnLoadCallback(drawChart_".$this->widget->widget_id.");
			function drawChart_".$this->widget->widget_id."() {
				var data = new google.visualization.DataTable();
				data.addColumn('string', 'Label');
				data.addColumn('number', 'Value');
				data.addRows(1);
				data.setValue(0, 0, '');
				data.setValue(0, 1, ".(int)$this->widget->main.");

				var chart = new google.visualization.Gauge(document.getElementById('graph_".$this->widget->widget_id."'));
				var options = {width: 190, height: 190, redFrom: 0, redTo: ".(int)($this->widget->average/2).",
						yellowFrom:".(int)($this->widget->average/2).", yellowTo: ".(int)$this->widget->average.",
						greenFrom:".(int)$this->widget->average.", greenTo: ".(int)($this->widget->average*2).", minorTicks: 5, min: 0, max: ".(int)($this->widget->average*2)."};
				chart.draw(data, options);
			}";
if (!HIKASHOP_PHP5) {
	$doc =& JFactory::getDocument();
}else{
	$doc = JFactory::getDocument();
}
$doc->addScriptDeclaration($js);
?>
<div id="graph_<?php echo $this->widget->widget_id; ?>" style="height: 210px;" align="center"></div>
