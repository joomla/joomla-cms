<?php
/**
 * $Id: default.php 11917 2009-05-29 19:37:05Z ian $
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

$cparams =& JComponentHelper::getParams('com_media');
?>

<?php if ( $this->params->get( 'show_page_title', 1 ) ) : ?>
<div class="componentheading<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
<?php echo $this->escape($this->params->get('page_title')); ?>
</div>
<?php endif; ?>
<div class="contentpane<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
<?php if ($this->category->image || $this->category->description) : ?>
	<div class="contentdescription<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
	<?php if ($this->params->get('image') != -1 && $this->params->get('image') != '') : ?>
		<img src="<?php echo $this->baseurl .'/'. 'images/stories' . '/'. $this->params->get('image'); ?>" align="<?php echo $this->params->get('image_align'); ?>" hspace="6" alt="<?php echo JText::_( 'Contacts' ); ?>" />
	<?php elseif ($this->category->image) : ?>
		<img src="<?php echo $this->baseurl .'/'. 'images/stories' . '/'. $this->category->image; ?>" align="<?php echo $this->category->image_position; ?>" hspace="6" alt="<?php echo JText::_( 'Contacts' ); ?>" />
	<?php endif; ?>
	<?php echo $this->category->description; ?>
	</div>
<?php endif; ?>
<script language="javascript" type="text/javascript">
	function tableOrdering( order, dir, task ) {
	var form = document.adminForm;

	form.filter_order.value 	= order;
	form.filter_order_Dir.value	= dir;
	document.adminForm.submit( task );
}
</script>
<form action="<?php echo $this->action; ?>" method="post" name="adminForm">
<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
	<thead>
		<tr>
			<td align="right" colspan="6">
			<?php if ($this->params->get('show_limit')) :
				echo JText::_('Display Num') .'&nbsp;';
				echo $this->pagination->getLimitBox();
			endif; ?>
			</td>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td align="center" colspan="6" class="sectiontablefooter<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
				<?php echo $this->pagination->getPagesLinks(); ?>
			</td>
		</tr>
		<tr>
			<td colspan="6" align="right">
				<?php echo $this->pagination->getPagesCounter(); ?>
			</td>
		</tr>
	</tfoot>
	<tbody>
	<?php if ($this->params->get( 'show_headings' )) : ?>
		<tr>
			<td width="5" align="right" class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
				<?php echo JText::_('Num'); ?>
			</td>
			<td height="20" class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
				<?php echo JHTML::_('grid.sort',  'Name', 'cd.name', $this->lists['order_Dir'], $this->lists['order'] ); ?>
			</td>
			<?php if ( $this->params->get( 'show_position' ) ) : ?>
			<td height="20" class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
				<?php echo JHTML::_('grid.sort',  'Position', 'cd.con_position', $this->lists['order_Dir'], $this->lists['order'] ); ?>
			</td>
			<?php endif; ?>
			<?php if ( $this->params->get( 'show_email' ) ) : ?>
			<td height="20" width="20%" class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
				<?php echo JText::_( 'Email' ); ?>
			</td>
			<?php endif; ?>
			<?php if ( $this->params->get( 'show_telephone' ) ) : ?>
			<td height="20" width="15%" class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
				<?php echo JText::_( 'Phone' ); ?>
			</td>
			<?php endif; ?>
			<?php if ( $this->params->get( 'show_mobile' ) ) : ?>
			<td height="20" width="15%" class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
				<?php echo JText::_( 'Mobile' ); ?>
			</td>
			<?php endif; ?>
			<?php if ( $this->params->get( 'show_fax' ) ) : ?>
				<td height="20" width="15%" class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
					<?php echo JText::_( 'Fax' ); ?>
				</td>
			<?php endif; ?>
		</tr>
	<?php endif; ?>
	<?php echo $this->loadTemplate('items'); ?>
</tbody>
</table>

<input type="hidden" name="option" value="com_contact" />
<input type="hidden" name="catid" value="<?php echo $this->category->id;?>" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="" />
</form>
</div>
