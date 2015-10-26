<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<?php
$itemOnARow = 0;
if(!HIKASHOP_BACK_RESPONSIVE) { ?>
<table class="adminform" style="width:100%;border-collapse:separate;border-spacing:5px">
<?php } else {
	echo '<div class="cpanel-widgets">';
}

foreach($this->widgets as $widget){
	if(empty($widget->widget_params->display)) continue;
	if(!hikashop_level(2)){
		if(isset($widget->widget_params->content) && $widget->widget_params->content=='partners' || $widget->widget_params->display=='map') continue;
		if(!hikashop_level(1) && in_array($widget->widget_params->display,array('gauge','pie'))) continue;
	}
	if($itemOnARow==0){
		if(!HIKASHOP_BACK_RESPONSIVE) {
			echo '<tr>';
		}else{
			echo '<div class="row-fluid">';
		}
	}
	$val = preg_replace('#[^a-z0-9]#i','_',strtoupper($widget->widget_name));
	$trans = JText::_($val);
	if($val!=$trans){
		$widget->widget_name = $trans;
	}
	if(hikashop_level(1)){
		if($this->manage){
			$widget->widget_name.= '
			<a href="'.hikashop_completeLink('report&task=edit&cid[]='.$widget->widget_id.'&dashboard=true').'">
				<img src="'.HIKASHOP_IMAGES.'edit.png" alt="edit"/>
			</a>';
		}
	}
	if(!HIKASHOP_BACK_RESPONSIVE) {
		echo '<td valign="top" style="border: 1px solid #CCCCCC"><fieldset style="border:0px" class="adminform"><legend>'.$widget->widget_name.'</legend>';
	}else{
		echo '<div class="span4" style="border: 1px solid #CCCCCC;min-height:280px"><fieldset style="border:0px" class="adminform"><legend>'.$widget->widget_name.'</legend>';
	}
	$this->widget =& $widget;
	if($widget->widget_params->display=='listing'){
		if(empty($widget->widget_params->content_view)) continue;
		$this->setLayout($widget->widget_params->content_view);
	}else if($widget->widget_params->display=='column' || $widget->widget_params->display=='line' || $widget->widget_params->display=='area'){
		$this->setLayout('chart');
	}else{
		$this->setLayout($widget->widget_params->display);
	}
	echo $this->loadTemplate();
	if(!HIKASHOP_BACK_RESPONSIVE) {
		echo '</fieldset></td>';
	}else{
		echo '</fieldset></div>';
	}
	$itemOnARow++;
	if($itemOnARow==3){
		if(!HIKASHOP_BACK_RESPONSIVE) {
			echo '</tr>';
		}else{
			echo '</div>';
		}
		$itemOnARow=0;
	}
}
if(!HIKASHOP_BACK_RESPONSIVE) {
?>
</table>
<?php } else {
	echo '</div>';
}
$this->setLayout('cpanel');
echo $this->loadTemplate();
