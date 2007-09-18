<?php // @version $Id$
defined('_JEXEC') or die('Restricted access');
?>

<?php if ($this->params->get('show_page_title')) : ?>
<h1 class="componentheading<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php echo $this->params->get('page_title'); ?>
</h1>
<?php endif; ?>

<?php if ($this->category->image || $this->category->description) : ?>
<div class="contentdescription<?php echo $this->params->get('pageclass_sfx'); ?>">

	<?php if ($this->params->get('image') != -1 && $this->params->get('image') != '') : ?>
	<img src="images/stories/<?php echo $this->params->get('image'); ?>" class="image_<?php echo $this->params->get('image_align'); ?>" alt="<?php echo JText::_('Contacts'); ?>" />
	<?php elseif($this->category->image): ?>
	<img src="images/stories/<?php echo $this->category->image; ?>" class="image_<?php echo $this->category->image_position; ?>" alt="<?php echo JText::_('Contacts'); ?>" />
	<?php endif; ?>

	<?php echo $this->category->description; ?>

	<?php if (($this->params->get('image') != -1 && $this->params->get('image') != '') || $this->category->image) : ?>
	<div class="wrap_image">&nbsp;</div>
	<?php endif; ?>

</div>
<?php endif; ?>

<script language="javascript" type="text/javascript">
function tableOrdering( order, dir, task )
{
	var form = document.adminForm;

	form.filter_order.value	 = order;
	form.filter_order_Dir.value	= dir;
	document.adminForm.submit( task );
}
</script>

<form action="<?php echo $this->action; ?>" method="post" name="adminForm">

	<?php if ($this->params->get('display')) : ?>
	<div class="display">
		<?php echo JText::_('Display Num'); ?>&nbsp;
	</div>
	<?php endif; ?>

	<input type="hidden" name="catid" value="<?php echo $this->category->id; ?>" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="" />

</form>

<table class="category<?php echo $this->params->get('pageclass_sfx'); ?>">

	<?php if ($this->params->get('show_headings')) : ?>
	<tr>
		<th id="Count" class="sectiontableheader<?php echo $this->params->get('pageclass_sfx'); ?>">
			<?php echo JText::_('Num'); ?>
		</th>

		<?php if ($this->params->get('show_position')) : ?>
		<th id="Position" class="sectiontableheader<?php echo $this->params->get('pageclass_sfx'); ?>">
			<?php echo JHTML::_('grid.sort', 'Position', 'cd.con_position', $this->lists['order_Dir'], $this->lists['order'] ); ?>
		</th>
		<?php endif; ?>

		<th id="Name" class="sectiontableheader<?php echo $this->params->get('pageclass_sfx'); ?>">
			<?php echo JHTML::_('grid.sort', 'Name', 'cd.name', $this->lists['order_Dir'], $this->lists['order'] ); ?>
		</th>

		<?php if ($this->params->get('show_email')) : ?>
		<th id="Mail" class="sectiontableheader<?php echo $this->params->get('pageclass_sfx'); ?>">
			<?php echo JText::_('Email'); ?>
		</th>
		<?php endif; ?>

		<?php if ( $this->params->get('show_telephone')) : ?>
		<th id="Phone" class="sectiontableheader<?php echo $this->params->get('pageclass_sfx'); ?>">
			<?php echo JText::_('Phone'); ?>
		</th>
		<?php endif; ?>

		<?php if ($this->params->get('show_mobile')) : ?>
		<th id="mobile" class="sectiontableheader<?php echo $this->params->get('pageclass_sfx'); ?>">
			<?php echo JText::_('Mobile'); ?>
		</th>
		<?php endif; ?>

		<?php if ( $this->params->get('show_fax')) : ?>
		<th id="Fax" class="sectiontableheader<?php echo $this->params->get('pageclass_sfx'); ?>">
			<?php echo JText::_('Fax'); ?>
		</th>
		<?php endif; ?>
	</tr>
	<?php endif; ?>
	
	<?php echo $this->loadTemplate('items'); ?>
</table>

<p class="counter">
	<?php echo $this->pagination->getPagesCounter(); ?>
</p>

<?php echo $this->pagination->getPagesLinks(); ?>