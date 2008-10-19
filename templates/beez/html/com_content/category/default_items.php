<?php // @version: $Id$
defined('_JEXEC') or die('Restricted access');
?>

<script language="javascript" type="text/javascript">
<!--
function tableOrdering( order, dir, task )
{
var form = document.adminForm;

form.filter_order.value = order;
form.filter_order_Dir.value = dir;
document.adminForm.submit( task );
}
// -->
</script>

<form action="<?php echo $this->action; ?>" method="post" name="adminForm">

<?php if ($this->params->get('filter')) : ?>
<div class="filter">
	<p>
		<?php echo JText::_('Filter'); ?>&nbsp;
		<input type="text" name="filter" value="<?php echo $this->lists['filter']; ?>" class="inputbox" onchange="document.adminForm.submit();" />
	</p>
</div>
<?php endif; ?>

<?php if ($this->params->get('show_pagination_limit')) : ?>
<div class="display">
	<?php echo JText::_('Display Num'); ?>&nbsp;
	<?php echo $this->pagination->getLimitBox(); ?>
</div>
<?php endif; ?>

<table class="category">

	<?php if ($this->params->get('show_headings')) : ?>
	<tr>
		<th class="sectiontableheader<?php echo $this->params->get('pageclass_sfx'); ?>" id="count">
			<?php echo JText::_('Num'); ?>
		</th>

		<?php if ($this->params->get('show_title')) : ?>
		<th class="sectiontableheader<?php echo $this->params->get('pageclass_sfx'); ?>" id="tableOrdering">
			<?php echo JHTML::_('grid.sort', 'Item Title', 'a.title', $this->lists['order_Dir'], $this->lists['order']); ?>
		</th>
		<?php endif; ?>

		<?php if ($this->params->get('show_date')) : ?>
		<th class="sectiontableheader<?php echo $this->params->get('pageclass_sfx'); ?>" id="tableOrdering2">
			<?php echo JHTML::_('grid.sort', 'Date', 'a.created', $this->lists['order_Dir'], $this->lists['order']); ?>
		</th>
		<?php endif; ?>

		<?php if ($this->params->get('show_author')) : ?>
		<th class="sectiontableheader<?php echo $this->params->get('pageclass_sfx'); ?>" id="author">
			<?php echo JHTML::_('grid.sort', 'Author', 'author', $this->lists['order_Dir'], $this->lists['order']); ?>
		</th>
		<?php endif; ?>

		<?php if ($this->params->get('show_hits')) : ?>
		<th align="center" class="sectiontableheader<?php echo $this->params->get('pageclass_sfx'); ?>" width="5%" nowrap="nowrap" id="hits">
			<?php echo JHTML::_('grid.sort', 'Hits', 'a.hits', $this->lists['order_Dir'], $this->lists['order']); ?>
		</th>
		<?php endif; ?>
	</tr>
	<?php endif; ?>

	<?php foreach ($this->items as $item) : ?>
	<tr class="sectiontableentry<?php echo ($item->odd + 1) . $this->params->get('pageclass_sfx'); ?>">
		<td headers="count">
			<?php echo $this->pagination->getRowOffset($item->count); ?>
		</td>

		<?php if ($this->params->get('show_title')) : ?>
		<td headers="tableOrdering">
			<?php if ($item->access <= $this->user->get('aid', 0)) : ?>
				<a href="<?php echo $item->link; ?>">
					<?php echo $this->escape($item->title); ?></a>
				<?php echo JHTML::_('icon.edit', $item, $this->params, $this->access);
			else :
				echo $item->title; ?> :
				<a href="<?php echo JRoute::_('index.php?option=com_user&task=register'); ?>">
					<?php echo JText::_('Register to read more...'); ?></a>
			<?php endif; ?>
		</td>
		<?php endif; ?>

		<?php if ($this->params->get('show_date')) : ?>
		<td  headers="tableOrdering2">
			<?php echo $item->created; ?>
		</td>
		<?php endif; ?>

		<?php if ($this->params->get('show_author')) : ?>
		<td headers="author">
			<?php echo $item->created_by_alias ? $item->created_by_alias : $item->author; ?>
		</td>
		<?php endif; ?>

		<?php if ($this->params->get('show_hits')) : ?>
		<td headers="hits">
			<?php echo $item->hits ? $item->hits : '-'; ?>
		</td>
		<?php endif; ?>

	</tr>
	<?php endforeach; ?>

</table>

<?php if ($this->params->get('show_pagination')) : ?>
<p class="counter">
	<?php echo $this->pagination->getPagesCounter(); ?>
</p>
<?php echo $this->pagination->getPagesLinks();
endif; ?>

<input type="hidden" name="id" value="<?php echo $this->category->id; ?>" />
<input type="hidden" name="sectionid" value="<?php echo $this->category->sectionid; ?>" />
<input type="hidden" name="task" value="<?php echo $this->lists['task']; ?>" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="" />
</form>
