<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tagsxc v
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.framework');

// Get the user object.
$user = JFactory::getUser();

// Check if user is allowed to add/edit based on tags permissions.
// Do we really have to make it so people can see unpublished tags???
$canEdit = $user->authorise('core.edit', 'com_tags');
$canCreate = $user->authorise('core.create', 'com_tags');
$canEditState = $user->authorise('core.edit.state', 'com_tags');
$items = $this->items;
$n = count($this->items);
?>

<?php if ($this->items == false || $n == 0) : ?>
	<p> <?php echo JText::_('COM_TAGS_NO_ITEMS'); ?></p></div>
<?php else : ?>

	<ul class="category list-striped list-condensed">
		<?php foreach ($items as $i => $item) : ?>
			<?php if ((!empty($item->core_access)) && in_array($item->core_access, $this->user->getAuthorisedViewLevels())) : ?>
				<?php if ($item->core_state == 0) : ?>
					<li class="system-unpublished cat-list-row<?php echo $i % 2; ?>">
				<?php else: ?>
					<li class="cat-list-row<?php echo $i % 2; ?>" >
					<h3>
						<a href="<?php echo JRoute::_(TagsHelperRoute::getItemRoute($item->content_item_id, $item->core_alias, $item->core_catid, $item->core_language, $item->type_alias, $item->router)); ?>">
							<?php echo $this->escape($item->core_title); ?>
						</a>
					</h3>
				<?php endif; ?>
				<?php $images  = json_decode($item->core_images);?>
				<?php if ($this->params->get('tag_list_show_item_image', 1) == 1 && !empty($images->image_intro)) :?>
					<img src="<?php echo htmlspecialchars($images->image_intro);?>" alt="<?php echo htmlspecialchars($images->image_intro_alt); ?>">
				<?php endif; ?>
				<?php if ($this->params->get('tag_list_show_item_description', 1)) : ?>
					<span class="tag-body">
						<?php echo JHtml::_('string.truncate', $item->core_body, $this->params->get('tag_list_item_maximum_characters')); ?>
					</span>
				<?php endif; ?>
					</li>
			<?php endif;?>
			<div class="clearfix"></div>
		<?php endforeach; ?>
	</ul>

	<?php if ($this->state->get('show_pagination')) : ?>
	 <div class="pagination">
		<?php if ($this->params->def('show_pagination_results', 1)) : ?>
			<p class="counter">
				<?php echo $this->pagination->getPagesCounter(); ?>
			</p>
		<?php endif; ?>
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>
	<?php endif; ?>

<?php endif; ?>
