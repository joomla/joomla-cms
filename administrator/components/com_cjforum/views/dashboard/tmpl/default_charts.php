<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();
JFactory::getDocument()->addScript('https://www.google.com/jsapi');

$values = array();
foreach ($this->topicCount as $date=>$value)
{
	$values[$date]['topics'] = $value['topics'];
}
foreach ($this->replyCount as $date=>$value)
{
	$values[$date]['replies'] = $value['replies'];
}

$dailyStats = array();
foreach ($values as $date=>$value)
{
	$topics = isset($value['topics']) ? (int) $value['topics'] : 0;
	$replies = isset($value['replies']) ? (int) $value['replies'] : 0;
	
	$dailyStats[] = "[new Date('".$date."'),".$topics.",".$replies."]";
}

$countries = array();
foreach ($this->geoReport as $country=>$value)
{
	$countries[] = "['".$value['country_name']."',".$value['posts']."]";
}
?>
<div role="tabpanel">
	<ul class="nav nav-tabs" role="tablist">
		<li role="presentation" class="active">
			<a href="#geo" aria-controls=geo role="tab" data-toggle="tab">
				<i class="fa fa-globe"></i> <?php echo JText::_('COM_CJFORUM_GEO_ACTIVITY');?>
			</a>
		</li>
		<li role="presentation">
			<a href="#trends" aria-controls="trends" role="tab" data-toggle="tab">
				<i class="fa fa-line-chart"></i> <?php echo JText::_('COM_CJFORUM_DAILY_TRENDS');?>
			</a>
		</li>
	</ul>
	<div class="tab-content">
		<div role="tabpanel" class="tab-pane active" id="geo">
			<div id="geo-location-report" style="width: 100%; height: 300px; max-height: 300px;"></div>
			<script type="text/javascript">
			google.load("visualization", "1.1", {packages:["geochart", "annotatedtimeline"]});
			google.setOnLoadCallback(drawRegionsMap);
			function drawRegionsMap() {
				var data = google.visualization.arrayToDataTable([['Country', 'Posts'], <?php echo implode(',', $countries)?>]);
				var options = {};
				var chart = new google.visualization.GeoChart(document.getElementById('geo-location-report'));
				chart.draw(data, options);
			}
			</script>
		</div>
		<div role="tabpanel" class="tab-pane active" id="trends">
			<div id="daily-stats-chart" style="width: 100%; height: 300px;"></div>
			<script type="text/javascript">
				google.setOnLoadCallback(drawDailyChart);

				function drawDailyChart() {
					var data = new google.visualization.DataTable();
					data.addColumn('date', "<?php echo JText::_('JDATE')?>");
					data.addColumn('number', "<?php echo JText::_('COM_CJFORUM_TOPICS')?>");
					data.addColumn('number', "<?php echo JText::_('COM_CJFORUM_REPLIES')?>");
					data.addRows([<?php echo implode(',', $dailyStats);?>]);

					var options = {
			          hAxis: {title: '<?php echo JText::_('JDATE');?>'},
			          legend: { position: 'bottom' },
			          displayAnnotations: false
			        };

			        var dailychart = new google.visualization.AnnotatedTimeLine(document.getElementById('daily-stats-chart'));
			        dailychart.draw(data, options);
			        document.getElementById('trends').className = 'tab-pane';
			      }
			</script>
		</div>
	</div>
</div>
	
<p></p>