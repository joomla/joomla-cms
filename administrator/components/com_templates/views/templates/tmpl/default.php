<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_templates
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

$user = & JFactory :: getUser();

JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
?>

<form action="<?php echo JRoute::_('index.php?option=com_templates&view=templates'); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSearch_Filter_Label'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->state->get('filter.search'); ?>" title="<?php echo JText::_('Templates_Templates_Filter_Search_Desc'); ?>" />
			<button type="submit"><?php echo JText::_('JSearch_Filter_Submit'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSearch_Filter_Clear'); ?></button>
		</div>
		<div class="filter-select fltrt">
			<label for="filter_client_id">
				<?php echo JText::_('Templates_Filter_Client'); ?>
			</label>
			<select name="filter_client_id" class="inputbox" onchange="this.form.submit()">
				<?php echo JHtml::_('select.options', TemplatesHelper::getClientOptions(), 'value', 'text', $this->state->get('filter.client_id'));?>
			</select>
		</div>
	</fieldset>
	<div class="clr"> </div>

	<table class="adminlist">
		<thead>
			<tr>
			<th class="col1template"> </th>
				<th>
					<?php echo JHtml::_('grid.sort', 'Templates_Heading_Template', 'a.element', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				
				<th width="10%" class="center">
					<?php echo JText::_('Version'); ?>
				</th>
				<th width="15%">
					<?php echo JText::_('Date'); ?>
				</th>
				<th width="25%" >
					<?php echo JText::_('Author'); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="8">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => $item) :
			$img_path = ($item->client_id == 1 ? JURI::root().'administrator' : JURI::root()).'/templates/'.$item->element.'/template_thumbnail.png';
			$imgprev_path = ($item->client_id == 1 ? JURI::root().'administrator' : JURI::root()).'/templates/'.$item->element.'/template_preview.png';
		?>
			<tr class="row<?php echo $i % 2; ?>">
				<td>
					<a href="<?php echo $imgprev_path; ?>" class="modal">
						<img src="<?php echo $img_path; ?>" alt="<?php echo JText::_('Templates_No_preview');?>" title="<?php echo JText::_('Templates_Click_to_enlarge');?>" /></a>
				</td>
				<td>
					<a href="<?php echo JRoute::_('index.php?option=com_templates&view=template&id='.(int) $item->extension_id); ?>">
						<?php echo $item->name;?></a>
				</td>
				<td class="center">
					<?php echo $this->escape($item->xmldata->get('version')); ?>
				</td>
				<td class="center">
					<?php echo $this->escape($item->xmldata->get('creationdate')); ?>
				</td>
				<td>
					<?php if ($author = $item->xmldata->get('author')) : ?>
						<?php echo $this->escape($author); ?>
					<?php else : ?>
						-
					<?php endif; ?>
					<?php if ($email = $item->xmldata->get('authorEmail')) : ?>
						<br /><?php echo $this->escape($email); ?>
					<?php endif; ?>
					<?php if ($url = $item->xmldata->get('authorUrl')) : ?>
						<br />
						<a href="<?php echo $this->escape($url); ?>">
							<?php echo $this->escape($url); ?></a>
					<?php endif; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<?php echo JHtml::_('form.token'); ?>
</form>
