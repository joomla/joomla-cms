<?php
/**
 * @version		$Id: featured_item.php 12416 2009-07-03 08:49:14Z eddieajau $
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Create a shortcut for params.
$params = &$this->item->params;
?>

<?php if ($this->item->state == 0) : ?>
<div class="system-unpublished">
<?php endif; ?>
<?php if ($params->get('show_title')) : ?>
	<h2>
		<?php if ($params->get('link_titles') && $params->get('access-view')) : ?>
			<a href="<?php echo JRoute::_(ContentRoute::article($this->item->slug, $this->item->catslug)); ?>">
			<?php echo $this->escape($this->item->title); ?></a>
		<?php else : ?>
			<?php echo $this->escape($this->item->title); ?>
		<?php endif; ?>
	</h2>
<?php endif; ?>

<?php if ($params->get('show_print_icon') || $params->get('show_email_icon') || $params->get('access-edit')) : ?>
	<ul class="actions">
		<?php if ($params->get('show_print_icon')) : ?>
		<li class="print-icon">
			<?php echo JHtml::_('icon.print_popup', $this->item, $params); ?>
		</li>
		<?php endif; ?>
		<?php if ($params->get('show_email_icon')) : ?>
		<li class="email-icon">
			<?php echo JHtml::_('icon.email', $this->item, $params); ?>
		</li>
		<?php endif; ?>
		<?php if ($this->user->authorise('core.edit', 'com_content.category.'.$this->item->id)) : ?>
		<li class="edit-icon">
			<?php echo JHtml::_('icon.edit', $this->item, $params); ?>
		</li>
		<?php endif; ?>
	</ul>
<?php endif; ?>

<?php if (!$params->get('show_intro')) : ?>
	<?php echo $this->item->event->afterDisplayTitle; ?>
<?php endif; ?>

<?php echo $this->item->event->beforeDisplayContent; ?>

<?php // to do not that elegant would be nice to group the params ?>

<?php if (($params->get('show_author')) or ($params->get('show_category')) or ($params->get('show_create_date')) or ($params->get('show_modify_date')) or ($params->get('show_publish_date'))) : ?>
 <dl class="article-info">
 <dt class="article-info-term"><?php  echo JText::_('JContent_Article_Infos'); ?></dt>
<?php endif; ?>
<?php if ($params->get('show_category')) : ?>
<dd class="category-name"><?php  echo JText::_('JContent_Category'); ?>
				<?php if ($params->get('link_category')) : ?>
					<?php echo '<a href="'.JRoute::_(ContentRoute::category($this->item->catslug)).'">'; ?>
					<?php echo $this->escape($this->item->category_title); ?></a>
					<?php else : ?>
					<?php echo $this->escape($this->item->category_title); ?>
				<?php endif; ?>
	</dd>
<?php endif; ?>
<?php if ($params->get('show_create_date')) : ?>
		<dd class="create">
		<?php echo JText::sprintf('CONTENT_CREATED_DATE', JHtml::_('date', $this->item->created, JText::_('DATE_FORMAT_LC2'))); ?>
		</dd>
<?php endif; ?>
<?php if ($params->get('show_modify_date')) : ?>
		<dd class="modified">
		<?php echo JText::sprintf('LAST_UPDATED2', JHtml::_('date', $this->item->modified, JText::_('DATE_FORMAT_LC2'))); ?>
		</dd>
<?php endif; ?>
<?php if ($params->get('show_publish_date')) : ?>
		<dd class="published">
		<?php echo JText::sprintf('PUBLISHED_DATE', JHtml::_('date', $this->item->publish_up, JText::_('DATE_FORMAT_LC2'))); ?>
		</dd>
<?php endif; ?>
<?php if ($params->get('show_author') && !empty($this->item->author_name)) : ?>
	<dd class="createdby">
		<?php $author=($this->item->created_by_alias ? $this->item->created_by_alias : $this->item->author_name);?>
		<?php echo JText::sprintf('Written_by', $author); ?>
		</dd>
	<?php endif; ?>
<?php if (($params->get('show_author')) or ($params->get('show_category')) or ($params->get('show_create_date')) or ($params->get('show_modify_date')) or ($params->get('show_publish_date'))) : ?>
 </dl>
<?php endif; ?>

<?php echo $this->item->introtext; ?>

<?php if ($params->get('show_readmore') && $this->item->readmore) :
	if ($params->get('access-view')) :
		$link = JRoute::_(ContentRoute::article($this->item->slug, $this->item->catslug));
	else :
		$link = JRoute::_("index.php?option=com_users&view=login");
	endif;
?>
		<p class="readmore">
				<a href="<?php echo $link; ?>">
						<?php if (!$params->get('access-view')) :
								echo JText::_('REGISTER_TO_READ_MORE');
						elseif ($readmore = $params->get('alternative_readmore')) :
								echo $readmore;
						else :
								echo JText::sprintf('READ_MORE', $this->escape($this->item->title));
						endif; ?></a>
		</p>
<?php endif; ?>

<?php if ($this->item->state == 0) : ?>
</div>
<?php endif; ?>

<div class="item-separator"></div>
<?php echo $this->item->event->afterDisplayContent; ?>
