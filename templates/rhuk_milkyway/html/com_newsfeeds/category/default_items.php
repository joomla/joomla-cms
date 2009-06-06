<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<form action="<?php echo JRoute::_('index.php?view=category&id='.$this->category->slug); ?>" method="post" name="adminForm">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<?php if ($this->params->get('show_limit')) : ?>
<tr>
	<td align="right" colspan="4">
	<?php
		echo JText::_('Display Num') .'&nbsp;';
		echo $this->pagination->getLimitBox();
	?>
	</td>
</tr>
<?php endif; ?>
<?php if ( $this->params->get( 'show_headings' ) ) : ?>
<tr>
	<td class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>" align="right" width="5">
		<?php echo JText::_('Num'); ?>
	</td>
	<?php if ( $this->params->get( 'show_name' ) ) : ?>
	<td height="20" width="90%" class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
		<?php echo JText::_( 'Feed Name' ); ?>
	</td>
	<?php endif; ?>
	<?php if ( $this->params->get( 'show_articles' ) ) : ?>
	<td height="20" width="10%" class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>" align="center" nowrap="nowrap">
		<?php echo JText::_( 'Num Articles' ); ?>
	</td>
	<?php endif; ?>
 </tr>
<?php endif; ?>
<?php foreach ($this->items as $item) : ?>
<tr class="sectiontableentry<?php echo $item->odd + 1; ?>">
	<td align="right" width="5">
		<?php echo $item->count + 1; ?>
	</td>
	<td height="20" width="90%">
		<a href="<?php echo $item->link; ?>" class="category<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
			<?php echo $this->escape($item->name); ?></a>
	</td>
	<?php if ( $this->params->get( 'show_articles' ) ) : ?>
	<td height="20" width="10%" align="center">
		<?php echo $item->numarticles; ?>
	</td>
	<?php endif; ?>
</tr>
<?php endforeach; ?>
<tr>
	<td align="center" colspan="4" class="sectiontablefooter<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
	<?php

		echo $this->pagination->getPagesLinks();
	?>
	</td>
</tr>
<tr>
	<td colspan="4" align="right">
		<?php echo $this->pagination->getPagesCounter(); ?>
	</td>
</tr>
</table>
</form>
