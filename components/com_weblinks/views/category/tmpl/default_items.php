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

// TODO: Optimise some of the fixed params in the loops
?>
<script  type="text/javascript">
	function tableOrdering(order, dir, task) {
	var form = document.adminForm;

	form.filter_order.value		= order;
	form.filter_order_Dir.value	= dir;
	document.adminForm.submit(task);
}
</script>



 <form action="<?php echo JFilterOutput::ampReplace(JFactory::getURI()); ?>" method="post" name="adminForm">
	 <div class="filter">
		<?php echo JText::_('Display Num'); ?>
		<?php echo $this->pagination->getLimitBox(); ?>
	 </div>
	<input type="hidden" name="filter_order" value="<?php echo $this->state->get('list.ordering'); ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->get('list.direction'); ?>" />
 </form>
<table class="weblinks">
<?php if ($this->params->def('show_headings', 1)) : ?>
	<thead>
		<tr>
			<th class="num">
				<?php echo JText::_('Num'); ?>
			</th>
			<th class="title">
					<?php echo JHtml::_('grid.sort',  'Web Link', 'title', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
			</th>
			<?php if ($this->params->get('show_link_hits')) : ?>
			<th class="hits">
					<?php echo JHtml::_('grid.sort',  'Hits', 'hits', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
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
										<?php echo JHtml::_('image',  'system/'.$this->params->get('link_icons', 'weblink.png'), JText::_('Link'), NULL, true);?>
								<?php endif; ?>

								<?php
										// Compute the correct link

										$menuclass = 'category'.$this->params->get('pageclass_sfx');
										$link		= JRoute::_('index.php?task=weblink.go&&id='. $item->id);
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
														<?php echo		$this->escape($item->title). ' </a>' ;
																 break;

												default:
														// open in parent window
																echo '<a href="'.  $link . '\" class="'. $menuclass .'" rel="nofollow">'.
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
				<?php if($this->pagination->get('pages.total')>1): ?>
							<div class="pagination">

											<p><?php echo $this->pagination->getPagesCounter(); ?></p>
											<?php echo $this->pagination->getPagesLinks(); ?>

							</div>
				<?php endif; ?>



