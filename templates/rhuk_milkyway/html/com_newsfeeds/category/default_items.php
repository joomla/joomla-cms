<?php
/**
 * @version		
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die; 
JHtml::core();

$n = count($this->items);
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction'); ?>

<?php if (empty($this->items)) : ?>
	<p> <?php echo JText::_('COM_NEWSFEEDS_NO_ARTICLES'); ?>	 </p>
<?php else : ?>

	<form action="<?php echo JRoute::_('index.php?view=category&id='.$this->category->slug); ?>" method="post" name="adminForm" id="adminForm">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<?php if ($this->params->get('show_limit')) : ?>
	<tr>
		<td align="right" colspan="4">
		<?php
			echo JText::_('JGLOBAL_DISPLAY_NUM') .'&#160;';
			echo $this->pagination->getLimitBox();
		?>
		</td>
	</tr>
	<?php endif; ?>
	<?php if ( $this->params->get( 'show_headings' ) ) : ?>
	<tr>
		<td class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>" align="right" width="5">
			<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>
		</td>
		<?php if ( $this->params->get( 'show_name' ) ) : ?>
			<td height="20" width="90%" class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
				<?php echo JText::_( 'COM_NEWSFEEDS_FEED_NAME' ); ?>
			</td>
		<?php endif; ?>
		<?php if ( $this->params->get( 'show_articles' ) ) : ?>
			<td height="20" width="10%" class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>" align="center" class="nowrap">
				<?php echo JText::_( 'COM_NEWSFEEDS_NUM_ARTICLES' ); ?>
			</td>
		<?php endif; ?>
	 </tr>
	<?php endif; ?>
		<?php foreach ($this->items as $i => $item) : ?>
		<tr class="sectiontableentry<?php echo $i % 2 ? 'odd' : 'even';?>">
			<td align="right" width="5">
				<?php echo $i; ?>
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
<?php endif; ?>