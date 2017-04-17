<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><fieldset>
	<div class="toolbar" id="toolbar" style="float: right;">
		<button class="btn" type="button" onclick="submitbutton('save');"><img src="<?php echo HIKASHOP_IMAGES; ?>save.png"/><?php echo JText::_('OK'); ?></button>
	</div>
</fieldset>
<div class="iframedoc" id="iframedoc"></div>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<table class="admintable" cellspacing="1">
		<tr>
			<td class="key">
				<?php echo JText::_( 'HIKA_NAME' ); ?>
			</td>
			<td>
				<input name="data[widget][widget_name]" value="<?php echo $this->escape(@$this->element->widget_name); ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'CONTENT' ); ?>
			</td>
			<td>
				<?php echo $this->widgetContent->display('data[widget][widget_params][content]',@$this->element->widget_params->content); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_( 'DISPLAY' ); ?>
			</td>
			<td>
				<?php echo $this->widgetDisplay->display('data[widget][widget_params][display]',@$this->element->widget_params->display); ?>
			</td>
		</tr>
		<tr id="widget_date">
			<td class="key" >
				<?php echo JText::_( 'DATE_TYPE' );// only for orders ?>
			</td>
			<td>
				<?php echo $this->dateType->display('data[widget][widget_params][date_type]',@$this->element->widget_params->date_type); ?>
			</td>
		</tr>
		<tr id="widget_group">
			<td class="key" >
				<?php echo JText::_( 'DATE_GROUP' );//only for graph and gauge ?>
			</td>
			<td>
				<?php echo $this->dateGroup->display('data[widget][widget_params][date_group]',@$this->element->widget_params->date_group); ?>
			</td>
		</tr>
		<tr>
			<td class="key" >
				<?php echo JText::_( 'START_DATE' ); ?>
			</td>
			<td>
				<?php echo JHTML::_('calendar', hikashop_getDate((@$this->element->widget_params->start?@$this->element->widget_params->start:''),'%Y-%m-%d %H:%M'), 'data[widget][widget_params][start]','period_start','%Y-%m-%d %H:%M',array('size'=>'20')); ?>
			</td>
		</tr>
		<tr>
			<td class="key" >
				<?php echo JText::_( 'END_DATE' ); ?>
			</td>
			<td>
				<?php echo JHTML::_('calendar', hikashop_getDate((@$this->element->widget_params->end?@$this->element->widget_params->end:''),'%Y-%m-%d %H:%M'), 'data[widget][widget_params][end]','period_end','%Y-%m-%d %H:%M',array('size'=>'20')); ?>
			</td>
		</tr>
		<tr>
			<td class="key" >
				<?php echo JText::_( 'PERIOD' ); ?>
			</td>
			<td>
				<?php echo $this->delay->display('data[widget][widget_params][period]',(int)@$this->element->widget_params->period,3); ?>
			</td>
		</tr>
		<tr id="widget_status">
			<td class="key" >
				<?php echo JText::_( 'ORDER_STATUS' );// only for orders ?>
			</td>
			<td>
				<?php echo $this->status->display('data[widget][widget_params][status][]',@$this->element->widget_params->status,' multiple="multiple" size="5"',false); ?>
			</td>
		</tr>
		<tr id="widget_limit">
			<td class="key">
				<?php echo JText::_( 'LIMIT' );//only for listing ?>
			</td>
			<td>
				<input name="data[widget][widget_params][limit]" value="<?php echo $this->escape(@$this->element->widget_params->limit); ?>" onchange="if(this.value <1 || this.value > 50){ alert('Setting a negative value or a too high value for the limit might might broke the dashboard.'); this.value=7;}" />
			</td>
		</tr>
		<?php if(hikashop_level(2)){ ?>
			<tr id="widget_region">
				<td class="key">
					<?php echo JText::_( 'ZONE' );//only for map ?>
				</td>
				<td>
					<?php echo $this->region->display('data[widget][widget_params][region]',@$this->element->widget_params->region); ?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php echo JText::_('ENCODING_FORMAT'); ?>
				</td>
				<td>
					<?php echo $this->encoding->display("data[widget][widget_params][format]",@$this->element->widget_params->format); ?>
				</td>
			</tr>
		<?php } ?>
	</table>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="data[widget][widget_id]" value="<?php echo (int)@$this->element->widget_id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getVar('ctrl');?>" />
	<?php echo JHTML::_( 'form.token' );?>
</form>
