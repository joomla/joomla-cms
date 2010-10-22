<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_modules
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');
JHtml::_('behavior.tooltip');

$function = JRequest::getVar('function', 'jSelectPosition');
$ordering	= $this->state->get('list.ordering');
$direction	= $this->state->get('list.direction');
$clientId	= $this->state->get('filter.client_id');
$state		= $this->state->get('filter.state');
$template	= $this->state->get('filter.template');
$type		= $this->state->get('filter.type');
?>
<form action="<?php echo JRoute::_('index.php?option=com_modules&view=positions&layout=modal&tmpl=component&function='.$function.'&client_id=' .$clientId);?>" method="post" name="adminForm" id="adminForm">
	<fieldset class="filter clearfix">
		<div class="left">
			<label for="filter_search">
				<?php echo JText::_('JSearch_Filter_Label'); ?>
			</label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->state->get('filter.search'); ?>" size="30" title="<?php echo JText::_('COM_MODULES_FILTER_SEARCH_DESC'); ?>" />

			<button type="submit">
				<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();">
				<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>

		<div class="right">
			<select name="filter_state" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('modules.templateStates'), 'value', 'text', $state, true);?>
			</select>

			<select name="filter_type" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('COM_MODULES_OPTION_SELECT_TYPE');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('modules.types'), 'value', 'text', $type, true);?>
			</select>

			<select name="filter_template" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_TEMPLATE');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('modules.templates', $clientId), 'value', 'text', $template, true);?>
			</select>
		</div>
	</fieldset>

	<table class="adminlist">
		<thead>
			<tr>
				<th class="title">
					<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'position', $direction, $ordering); ?>
				</th>
				<th>
					<?php echo JHtml::_('grid.sort', 'COM_MODULES_HEADING_TEMPLATES', 'templates', $direction, $ordering); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="15">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i=>$position) : ?>
			<tr class="row<?php echo $i % 2;?>">
				<td>
					<a class="pointer" onclick="if (window.parent) window.parent.<?php echo $function;?>('<?php echo $position->position; ?>');"><?php echo $this->escape($position->position); ?></a>
				</td>
				<td>
					<?php if (isset($position->templates)):?>
					<a class="pointer" onclick="if (window.parent) window.parent.<?php echo $function;?>('<?php echo $position->position; ?>');">
						<ul>
						<?php foreach ($position->templates as $template):?>
							<li><?php echo JText::sprintf('COM_MODULES_MODULE_TEMPLATE_POSITION', $template->name, JText::_('TPL_'.$template->element.'_POSITION_'.preg_replace('/[^a-zA-Z0-9_]/','_', $position->position)));?></li>	
						<?php endforeach;?>
						</ul>
					</a>
					<?php endif;?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $ordering; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $direction; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
