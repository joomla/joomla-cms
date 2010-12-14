<?php
/**
 * @version		
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers');
$params = &$this->params;
?>
<ul id="archive-list" style="list-style: none;">
<?php foreach ($this->items as $item) : ?>
	<li class="row<?php echo ($item->odd +1 ); ?>">
		<h4 class="contentheading">
			<a href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($item->slug)); ?>">
				<?php echo $this->escape($item->title); ?></a>
		</h4>

		<?php if ((($params->get('show_parent_category')) || ($params->get('show_category')))) : ?>
			<div>
			<?php if ($params->get('show_parent_category')) : ?>
				<span>
				<?php if ($this->params->get('link_section')) : ?>
					<?php $title = $this->escape($item->parent_title); 
					$url = '<a href="'.JRoute::_(ContentHelperRoute::getCategoryRoute($item->parent_slug)).'">'.$title.'</a>'; ?>
					<?php if ($params->get('link_parent_category') && $item->parent_slug) : ?>
						<?php echo JText::sprintf('COM_CONTENT_PARENT', $url); ?>
						<?php else : ?>
						<?php echo JText::sprintf('COM_CONTENT_PARENT', $title); ?>				
					<?php endif; ?>

				<?php if ($this->params->get('show_category')) : ?>
					<?php echo ' - '; ?>
				<?php endif; ?>
				</span>
			<?php endif; ?>
			<?php	$title = $this->escape($item->category_title);
					$url = '<a href="' . JRoute::_(ContentHelperRoute::getCategoryRoute($item->catslug)) . '">' . $title . '</a>'; ?>
			<?php if ($params->get('link_category') && $item->catslug) : ?>
				<?php echo JText::sprintf('COM_CONTENT_CATEGORY', $url); ?>
				<?php else : ?>
				<?php echo JText::sprintf('COM_CONTENT_CATEGORY', $title); ?>
			<?php endif; ?>
			</div>
		<?php endif; ?>

		<h5 class="metadata">
		<?php if ($this->params->get('show_create_date')) : ?>
			<span class="created-date">
				<?php echo JText::_('Created') .': '.  JHTML::_( 'date', $item->created, JText::_('DATE_FORMAT_LC2')) ?>
			</span>
			<?php endif; ?>
				<?php if ($this->params->get('show_author')) : ?>
					<span class="author">
					<?php echo JText::_('JAUTHOR').': '; echo $this->escape($item->created_by_alias) ? $this->escape($item->created_by_alias) : $this->escape($item->author); ?>
				</span>
			<?php endif; ?>
		<?php endif; ?>
		<?php if ($params->get('show_create_date')) : ?>
				<li class="create">
				<?php echo JText::sprintf('COM_CONTENT_CREATED_DATE_ON', JHTML::_('date',$item->created, JText::_('DATE_FORMAT_LC2'))); ?>
				</li>
		<?php endif; ?>
		<?php if ($params->get('show_modify_date')) : ?>
				<li class="modified">
				<?php echo JText::sprintf('COM_CONTENT_LAST_UPDATED', JHTML::_('date',$item->modified, JText::_('DATE_FORMAT_LC2'))); ?>
				</li>
		<?php endif; ?>
		<?php if ($params->get('show_publish_date')) : ?>
				<li class="published">
				<?php echo JText::sprintf('COM_CONTENT_PUBLISHED_DATE', JHTML::_('date',$item->publish_up, JText::_('DATE_FORMAT_LC2'))); ?>
				</li>
		<?php endif; ?>
<?php if ($params->get('show_author') && !empty($item->author )) : ?>
	<li class="createdby"> 
		<?php $author =  $item->author; ?>
		<?php $author = ($item->created_by_alias ? $item->created_by_alias : $author);?>

			<?php if (!empty($item->contactid ) &&  $params->get('link_author') == true):?>
				<?php 	echo JText::sprintf('COM_CONTENT_WRITTEN_BY' , 
				 JHTML::_('link',JRoute::_('index.php?option=com_contact&view=contact&id='.$item->contactid),$author)); ?>

			<?php else :?>
				<?php echo JText::sprintf('COM_CONTENT_WRITTEN_BY', $author); ?>
			<?php endif; ?>
	</li>
<?php endif; ?>	
<?php if ($params->get('show_hits')) : ?>
		<li class="hits">
		<?php echo JText::sprintf('COM_CONTENT_ARTICLE_HITS', $item->hits); ?>
		</li>
<?php endif; ?>
<?php if (($params->get('show_author')) or ($params->get('show_category')) or ($params->get('show_create_date')) or ($params->get('show_modify_date')) or ($params->get('show_publish_date'))  or ($params->get('show_hits'))) :?>

<?php endif; ?>
			
		</h5>
		<div class="intro">
			<?php echo substr(strip_tags($item->introtext), 0, 255);  ?>...
		</div>
	</li>
<?php endforeach; ?>
</ul>
<div id="pagination">
	<span><?php echo $this->pagination->getPagesLinks(); ?></span>
	<span><?php echo $this->pagination->getPagesCounter(); ?></span>
</div>
