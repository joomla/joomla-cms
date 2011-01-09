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

// Get the user object.
//$user = JFactory::getUser();
$params = &$this->item->params;
// Check if user is allowed to add/edit based on content permissions.

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::core();

$n = count($this->items);
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction')
?>
<script type="text/javascript">

	function tableOrdering( order, dir, task )
	{
		var form = document.adminForm;

		form.filter_order.value = order;
		form.filter_order_Dir.value	= dir;
		document.adminForm.submit( task );
	}
</script>
<form action="<?php echo JFilterOutput::ampReplace(JFactory::getURI()->toString()); ?>" method="post" name="adminForm" id="adminForm">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<?php if ($this->params->get('filter') || $this->params->get('show_pagination_limit')) : ?>
<tr>
	<td colspan="5">
		<table>
		<tr>
		<?php if ($this->params->get('filter')) : ?>
			<td align="left" width="60%" class="nowrap">
				<?php echo JText::_($this->params->get('filter_type') . ' Filter').'&#160;'; ?>
				<input type="text" name="filter" value="<?php echo $this->escape($this->lists['filter']);?>" class="inputbox" onchange="document.adminForm.submit();" />
			</td>
		<?php endif; ?>
		<?php if ($this->params->get('show_pagination_limit')) : ?>
			<td align="right" width="40%" class="nowrap">
			<?php
				echo '&#160;&#160;&#160;'.JText::_('JGLOBAL_DISPLAY_NUM').'&#160;';
				echo $this->pagination->getLimitBox();
			?>
			</td>
		<?php endif; ?>
		</tr>
		</table>
	</td>
</tr>
<?php endif; ?>
<?php if ($this->params->get('show_headings')) :?>
<tr>
	<td class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>" align="right" width="5%">
		<?php echo JText::_('JGLOBAL_NUM'); ?>
	</td>
	<?php if ($this->params->get('show_title')) : ?>
	<td class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>" width="45%">
			<?php  echo JHTML::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder) ; ?>
	</td>
	<?php endif; ?>
	<?php if ($date = $this->params->get('list_show_date')) : ?>
	<td class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>" width="25%">
		<?php echo JHTML::_('grid.sort', 'COM_CONTENT_'.$date.'_DATE', 'a.created', $listDirn, $listOrder); ?>
	</td>
	<?php endif; ?>
	<?php if ($this->params->get('list_show_author',1)) : ?>
	<td class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>"  width="20%">
		<?php echo JHTML::_('grid.sort', 'JAUTHOR', 'author', $listDirn, $listOrder); ?>
	</td>
	<?php endif; ?>
	<?php if ($this->params->get('list_show_hits',1)) : ?>
	<td align="center" class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>" width="5%">
		<?php echo JHTML::_('grid.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
	</td>
	<?php endif; ?>
</tr>
<?php endif; ?>
<?php foreach ($this->items as $i => $article) : ?>
<tr class="sectiontableentry<?php echo  $this->escape($this->params->get('pageclass_sfx')); ?>" >
	
	<?php if (in_array($article->access, $this->user->getAuthorisedViewLevels())) : ?>
		<td align="right">
			<?php echo $i; ?>
		</td>
	<td>
			<a href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($article->slug, $article->catid)); ?>">
			<?php echo $this->escape($article->title); ?></a>
						<?php if ($article->params->get('access-edit')) : ?>
							<ul class="actions">
								<li class="edit-icon">
									<?php echo JHtml::_('icon.edit',$article, $params); ?>
								</li>
							</ul>
						<?php endif; ?>
	</td>
	<?php else : ?>
	<td>
		<?php
			echo $this->escape($item->title).' : ';
			$link = JRoute::_('index.php?option=com_user&view=login');
			$returnURL = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug, $item->sectionid), false);
			$fullURL = new JURI($link);
			$fullURL->setVar('return', base64_encode($returnURL));
		?>
		<a href="<?php echo $fullURL; ?>">
			<?php echo JText::_( 'Register to read more...' ); ?></a>
	</td>
	<?php endif; ?>

	<?php if ($this->params->get('show_date')) : ?>
	<td>
		<?php echo $item->created; ?>
	</td>
	<?php endif; ?>
	<?php if ($this->params->get('list_show_author',1) && !empty($article->author )) : ?>	
				<td class="createdby"> 
					<?php $author =  $article->author ?>
					<?php $author = ($article->created_by_alias ? $article->created_by_alias : $author);?>
	
						<?php if (!empty($article->contactid ) &&  $this->params->get('link_author') == true):?>
							<?php 	echo 
							 JHTML::_('link',JRoute::_('index.php?option=com_contact&view=contact&id='.$article->contactid),$author); ?>
			
						<?php else :?>
							<?php echo JText::sprintf('COM_CONTENT_WRITTEN_BY', $author); ?>
						<?php endif; ?>
				</td>
		<?php endif; ?>	
	<?php if ($this->params->get('list_show_hits',1)) : ?>
	<td align="center">
		<?php echo $article->hits; ?>
	</td>
	<?php endif; ?>
</tr>
<?php endforeach; ?>
<?php if ($this->params->get('show_pagination')) : ?>
<tr>
	<td colspan="5">&#160;</td>
</tr>
<tr>
	<td align="center" colspan="4" class="sectiontablefooter<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
		<?php echo $this->pagination->getPagesLinks(); ?>
	</td>
</tr>
<tr>
	<td colspan="5" align="right">
		<?php echo $this->pagination->getPagesCounter(); ?>
	</td>
</tr>
<?php endif; ?>
</table>

	<div>
		<!-- @TODO add hidden inputs -->
		<input type="hidden" name="filter_order" value="" />
		<input type="hidden" name="filter_order_Dir" value="" />
		<input type="hidden" name="limitstart" value="" />
	</div>
</form>

