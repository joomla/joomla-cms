<?php
/**
 * @version		$Id: default_items.php 13471 2009-11-12 00:38:49Z eddieajau
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::core();

$n = count($this->items);
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
?>

<?php if (empty($this->items)) : ?>
	<p> <?php echo JText::_('COM_WEBLINKS_NO_WEBLINKS'); ?></p>
<?php else : ?>

<form action="<?php echo JFilterOutput::ampReplace(JFactory::getURI()->toString()); ?>" method="post" name="adminForm">
	<fieldset class="filters">
	<legend class="hidelabeltxt"><?php echo JText::_('JGLOBAL_FILTER_LABEL'); ?></legend>
	<?php if ($this->params->get('show_pagination_limit')) : ?>
		<div class="display-limit">
			<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>
	<?php endif; ?>
	</fieldset>

	<table class="category">
		<?php if ($this->params->get('show_headings')==1) : ?>

		<thead><tr>
			<th class="num">
				<?php echo JText::_('COM_WEBLINKS_NUM'); ?>
			</th>
			<th class="title">
					<?php echo JHtml::_('grid.sort',  'COM_WEBLINKS_GRID_SORT', 'title', $listDirn, $listOrder); ?>
			</th>
			<?php if ($this->params->get('show_link_hits')) : ?>
			<th class="hits">
					<?php echo JHtml::_('grid.sort',  'JGLOBAL_HITS', 'hits', $listDirn, $listOrder); ?>
			</th>
			<?php endif; ?>
		</tr>
	</thead>
	<?php endif; ?>
	<tbody>
	<?php foreach ($this->items as $i => $item) : ?>
		<tr class="<?php echo $i % 2 ? 'odd' : 'even';?>">
			<td class="num">
				<?php echo $this->pagination->getRowOffset($i); ?>
			</td>
			<td class="title">
			<p>
				<?php if ($this->params->get('link_icons') <> -1) : ?>
					<?php echo JHTML::_('image','system/'.$this->params->get('link_icons', 'weblink.png'), JText::_('COM_WEBLINKS_LINK'), NULL, true);?>
				<?php endif; ?>
				<?php
					// Compute the correct link
					$menuclass = 'category'.$this->params->get('pageclass_sfx');
					$link = $item->link;
					switch ($item->params->get('target', $this->params->get('target')))
					{
						case 1:
							// open in a new window
							echo '<a href="'. $link .'" target="_blank" class="'. $menuclass .'" rel="nofollow">'.
								$this->escape($item->title) .'</a>';
							break;

						case 2:
							// open in a popup window
							echo "<a href=\"#\" onclick=\"javascript: window.open('". $link ."', '', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=780,height=550'); return false\" class=\"$menuclass\">".
								$this->escape($item->title) ."</a>\n";
							break;
						case 3:
							// TODO: open in a modal window
							JHtml::_('behavior.modal', 'a.modal'); ?>
							<a class="modal" title="<?php  echo $this->escape($item->title) ?> " href="<?php echo $link;?>"  rel="{handler: 'iframe', size: {x: 500, y: 506}}\"></a>
							<?php echo $this->escape($item->title). ' </a>' ;
							break;

						default:
							// open in parent window
							echo '<a href="'.  $link . '" class="'. $menuclass .'" rel="nofollow">'.
								$this->escape($item->title) . ' </a>';
							break;
					}
				?>
			</p>

			<?php if (($this->params->get('show_link_description')) AND ($item->description !='')): ?>
				<p>
				<?php echo nl2br($item->description); ?>
				</p>
			<?php endif; ?>
		</td>
		<?php if ($this->params->get('show_link_hits')) : ?>
		<td class="hits">
			<?php echo $item->hits; ?>
		</td>
		<?php endif; ?>
	</tr>
	<?php endforeach; ?>
</tbody>
</table>
	<?php if ($this->params->get('show_pagination')) : ?>
	 <div class="pagination">
	<?php if ($this->params->def('show_pagination_results', 1)) : ?>
						<p class="counter">
							<?php echo $this->pagination->getPagesCounter(); ?>
						</p>
   <?php endif; ?>
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>
	<?php endif; ?>
	<div>
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	</div>
</form>
<?php endif; ?>