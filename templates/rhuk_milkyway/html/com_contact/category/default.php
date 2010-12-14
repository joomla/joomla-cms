<?php
/**
 * @version		
 * @package		Joomla.Site
 * @subpackage	com_contact
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
$cparams = JComponentHelper::getParams('com_media');
JHtml::core();

$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
?>
<?php if ( $this->params->get( 'show_page_heading', 1 ) ) : ?>
<div class="componentheading<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
<?php echo $this->escape($this->params->get('page_title')); ?>
</div>
<?php endif; ?>
<div class="contentpane<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
<?php if ($this->params->def('show_description', 1) || $this->params->def('show_description_image', 1)) : ?>
	<div class="contentdescription<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
	<?php if ($this->params->get('image') != -1 && $this->params->get('image') != '') : ?>
		<img src="<?php echo $this->baseurl .'/'. 'images' . '/'. $this->params->get('image'); ?>" align="<?php echo $this->params->get('image_align'); ?>" hspace="6" alt="<?php echo JText::_( 'Contacts' ); ?>" />
	<?php elseif ($this->params->get('image')) : ?>
		<img src="<?php echo $this->baseurl .'/'. 'images' . '/'. $this->category->image; ?>" align="<?php echo $this->category->image_position; ?>" hspace="6" alt="<?php echo JText::_( 'Contacts' ); ?>" />
	<?php endif; ?>
	<?php echo $this->category->description; ?>
	</div>
<?php endif; ?>
<script type="text/javascript">
	function tableOrdering( order, dir, task ) {
	var form = document.adminForm;

	form.filter_order.value = order;
	form.filter_order_Dir.value	= dir;
	document.adminForm.submit( task );
}
</script>
<form action="<?php echo JFilterOutput::ampReplace(JFactory::getURI()->toString()); ?>" method="post" name="adminForm" id="adminForm">
<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
	<thead>
		<tr>
			<td align="right" colspan="6">
			<?php if ($this->params->get('show_limit')) :
				echo JText::_('JGLOBAL_DISPLAY_NUM') .'&#160;';
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

				<?php echo $this->pagination->getLimitBox(); ?>
			</td>
		</tr>
		<tr>	
		<td height="20" class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
				<?php echo JText::_('JGLOBAL_NUM'); ?>
		</td>		
			<td height="20" class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
								<?php echo JHtml::_('grid.sort', 'COM_CONTACT_CONTACT_EMAIL_NAME', 'a.name', $listDirn, $listOrder); ?>
			</td>
			<?php if ( $this->params->get( 'show_position' ) ) : ?>
			<td height="20" class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
				<?php echo JHtml::_('grid.sort', 'COM_CONTACT_POSITION', 'a.con_position', $listDirn, $listOrder); ?>
			</td>
			<?php endif; ?>
			<?php if ( $this->params->get( 'show_email' ) ) : ?>
			<td height="20" width="20%" class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
				<?php echo JText::_( 'JGLOBAL_EMAIL' ); ?>
			</td>
			<?php endif; ?>
			<?php if ( $this->params->get( 'show_telephone' ) ) : ?>
			<td height="20" width="15%" class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
				<?php echo JText::_( 'COM_CONTACT_TELEPHONE' ); ?>
			</td>
			<?php endif; ?>
			<?php if ( $this->params->get( 'show_mobile' ) ) : ?>
			<td height="20" width="15%" class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
				<?php echo JText::_( 'COM_CONTACT_MOBILE' ); ?>
			</td>
			<?php endif; ?>
			<?php if ( $this->params->get( 'show_fax' ) ) : ?>
				<td height="20" width="15%" class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
					<?php echo JText::_( 'COM_CONTACT_FAX' ); ?>
				</td>
			<?php endif; ?>
		</tr>
	<?php endif; ?>
	<?php echo $this->loadTemplate('items'); ?>
</tbody>
</table>

<?php if (!empty($this->children[$this->category->id])&& $this->maxLevel != 0) : ?>
<div class="cat-children">
	<h3><?php echo JText::_('JGLOBAL_SUBCATEGORIES') ; ?></h3>
	<?php echo $this->loadTemplate('children'); ?>
</div>
<?php endif; ?>
	<div>
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	</div>
<input type="hidden" name="option" value="com_contact" />
<input type="hidden" name="catid" value="<?php echo $this->category->id;?>" />

</form>
</div>
