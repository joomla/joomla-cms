<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$columns = 2;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

JFactory::getDocument()->addScriptDeclaration("
		var resetFilter = function() {
		document.getElementById('filter-search').value = '';
	}
");
?>
<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm"
	  id="adminForm" class="form-inline">
	<?php if ($this->params->get('show_headings') || $this->params->get('filter_field') || $this->params->get('show_pagination_limit')) : ?>
		<fieldset class="filters btn-toolbar">
			<?php if ($this->params->get('filter_field')) : ?>
				<div class="btn-group">
					<label class="filter-search-lbl element-invisible" for="filter-search">
						<?php echo JText::_('COM_TAGS_TITLE_FILTER_LABEL') . '&#160;'; ?>
					</label>
					<input type="text" name="filter-search" id="filter-search"
						   value="<?php echo $this->escape($this->state->get('list.filter')); ?>" class="inputbox"
						   onchange="document.getElementById('adminForm').submit();"
						   title="<?php echo JText::_('COM_TAGS_FILTER_SEARCH_DESC'); ?>"
						   placeholder="<?php echo JText::_('COM_TAGS_TITLE_FILTER_LABEL'); ?>"/>
					<button type="button" name="filter-search-button"
							title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"
							onclick="document..getElementById('adminForm').submit();" class="btn">
						<span class="icon-search"></span>
					</button>
					<button type="reset" name="filter-clear-button"
							title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" class="btn"
							onclick="resetFilter(); document.getElementById('adminForm').submit();">
						<span class="icon-remove"></span>
					</button>
				</div>
			<?php endif; ?>
			<?php if ($this->params->get('show_pagination_limit')) : ?>
				<div class="btn-group pull-right">
					<label for="limit" class="element-invisible">
						<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>
					</label>
					<?php echo $this->pagination->getLimitBox(); ?>
				</div>
			<?php endif; ?>

			<input type="hidden" name="filter_order" value=""/>
			<input type="hidden" name="filter_order_Dir" value=""/>
			<input type="hidden" name="limitstart" value=""/>
			<input type="hidden" name="task" value=""/>
			<div class="clearfix"></div>
		</fieldset>
	<?php endif; ?>

	<?php if (!$this->items) : ?>
		<p> <?php echo JText::_('COM_TAGS_NO_ITEMS'); ?></p>
	<?php else : ?>
		<?php $counter = 0; ?>
		<?php foreach ($this->items as $i => $item) : ?>
			<?php $rowcount = ((int) $i % (int) $columns) + 1; ?>
			<?php $type_alias = explode('.', $item->type_alias); ?>
			<?php if ($rowcount == 1) : ?>
				<?php $row = $counter / $columns; ?>
				<div class="items-row cols-<?php echo (int) $columns; ?> <?php echo 'row-' . $row; ?> row-fluid clearfix">
			<?php endif; ?>
			<div class="span<?php echo round((12 / $columns)); ?>">
				<div
					class="item column-<?php echo $rowcount; ?><?php echo $item->core_state == 0 ? ' system-unpublished' : ''; ?>">
					<?php $layout = new JLayoutFile('tagged_item.' . $type_alias[1], null, array('component' => $type_alias[0])); ?>
					<?php if (!$output = $layout->render(array('item' => $item))) : ?>
						<?php $output = JLayoutHelper::render('joomla.content.tagged_item', array('item' => $item)); ?>
					<?php endif; ?>
					<?php echo $output; ?>
				</div>
				<?php $counter++; ?>
			</div>
			<?php if (($rowcount == $columns) or ($counter == count($this->items))) : ?>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php endif; ?>
</form>
