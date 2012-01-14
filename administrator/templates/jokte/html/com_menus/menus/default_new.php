<?php
/**
 * @version		$Id: default.php 20416 2011-01-23 16:40:48Z infograf768 $
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');

$uri	= JFactory::getUri();
$return	= base64_encode($uri);
$user	= JFactory::getUser();
$userId	= $user->get('id');
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task != 'menus.delete' || confirm('<?php echo JText::_('COM_MENUS_MENU_CONFIRM_DELETE',true);?>')) {
			Joomla.submitform(task);
		}
	}
</script>
<?php if( $this->items ): ?>
<form action="<?php echo JRoute::_('index.php?option=com_menus&view=menus');?>" method="post" name="adminForm" id="adminForm">
<!--<?php if( $this->pagination->total > 0 ): ?><div id="pagination-top"><?php echo $this->pagination->getListFooter(); ?></div><?php endif; ?>-->
		
		<ol class="menu-list">

		<?php foreach ($this->items as $i => $item) :
			$canCreate	= $user->authorise('core.create',		'com_menus');
			$canEdit	= $user->authorise('core.edit',			'com_menus');
			$canChange	= $user->authorise('core.edit.state',	'com_menus');
		?>

		
			<li class="menu-list-item">
				<div>
					<h3>
						<a href="<?php echo JRoute::_('index.php?option=com_menus&view=items&menutype='.$item->menutype) ?> ">
							<?php echo $this->escape($item->title); ?>
						</a>
						<!--<span class="menu-type"><?php echo $this->escape($item->menutype); ?></span>-->
					</h3>
				
					<!--<span class="menu-desc"><?php echo $this->escape($item->description); ?></span>-->

					<!--<ul>
						<li class="hasTip" title="<?php echo JText::_('COM_MENUS_HEADING_PUBLISHED_ITEMS'); ?>"><a href="#"><?php echo $item->count_published; ?></a></li>
						<li class="hasTip" title="<?php echo JText::_('COM_MENUS_HEADING_UNPUBLISHED_ITEMS'); ?>"><a href="#"><?php echo $item->count_unpublished; ?></a></li>
						<li class="hasTip" title="<?php echo JText::_('COM_MENUS_HEADING_TRASHED_ITEMS'); ?>"><a href="#"><?php echo $item->count_trashed; ?></a></li>
					</ul>-->


					<a class="menu-modules" href="#">
						<!--see modules linked to the Menu-->
						<?php echo "<span>".count($this->modules[$item->menutype])." modules</span>";  ?>
					</a>
								
					<!--<a href="<?php echo JRoute::_('index.php?option=com_menus&view=items&menutype='.$item->menutype.'&filter_published=1');?>">
						<?php echo $item->count_published; ?>
					</a>				
					<a href="<?php echo JRoute::_('index.php?option=com_menus&view=items&menutype='.$item->menutype.'&filter_published=0');?>">
						<?php echo $item->count_unpublished; ?>
					</a>
					<a href="<?php echo JRoute::_('index.php?option=com_menus&view=items&menutype='.$item->menutype.'&filter_published=-2');?>">
						<?php echo $item->count_trashed; ?>
					</a>-->
					<!--<span class="menu-id"><?php echo JHtml::_('grid.id', $i, $item->id); ?></span>-->
			</div>
		</li>
		
	<?php endforeach; ?>

	</ol>	

	<!--<?php if( $this->pagination->total > 0): ?><div id="pagination-bottom"><?php echo $this->pagination->getListFooter(); ?></div><?php endif; ?>-->
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
<?php else: ?>
    <div class="noresults"><p><?php echo JText::_('COM_CONTENT_NO_ARTICLES_LABEL'); ?></p></div>
<?php endif; ?>