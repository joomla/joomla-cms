<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tags
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
$canEdit = $user->authorise('core.edit', 'com_tags');
$canCreate = $user->authorise('core.create', 'com_tags');
$canEditState = $user->authorise('core.edit.state', 'com_tags');

$columns = $this->params->get('tag_columns', 1);
// Avoid division by 0 and negative columns.
if ($columns < 1)
{
	$columns = 1;
}
$bsspans = floor(12 / $columns);
if ($bsspans < 1)
{
	$bsspans = 1;
}

$bscolumns = min($columns, floor(12 / $bsspans));
$n = count($this->items);
?>

<?php if ($this->items == false || $n == 0) : ?>
	<p> <?php echo JText::_('COM_TAGS_NO_TAGS'); ?></p>
<?php else : ?>
			<?php foreach ($this->items as $i => $item) : ?>
				<?php if ($n == 1 || $i == 0 || $bscolumns == 1 || $i % $bscolumns == 0) : ?>
						<ul class="thumbnails">
				<?php endif; ?>
					<?php if ((!empty($item->access)) && in_array($item->access, $this->user->getAuthorisedViewLevels())) : ?>
                            <?php $route = new TagsHelperRoute ?>
						<li class="cat-list-row<?php echo $i % 2; ?>" >
								<?php  echo '<h3> <a href="' . JRoute::_($route->getRoute($item->id . ':' . $item->alias)) . '">'
								. $this->escape($item->title) . '</a> </h3>';  ?>
						<?php endif; ?>
						<?php  if ($this->params->get('all_tags_show_tag_hits')) : ?>
							<span class="list-hits badge badge-info pull-right">
								<?php echo JText::sprintf('JGLOBAL_HITS_COUNT', $item->hits); ?>
							</span>
						<?php endif; ?>
						<?php  if ($this->params->get('all_tags_show_tag_image') && !empty($item->images)) : ?>
							<?php  $images  = json_decode($item->images); ?>
							<span class="tag-body">
								<?php if (!empty($images->image_intro)): ?>
									<?php $imgfloat = (empty($images->float_intro)) ? $this->params->get('float_intro') : $images->float_intro; ?>
									<div class="pull-<?php echo htmlspecialchars($imgfloat); ?> item-image"> <img
									<?php if ($images->image_intro_caption):
										echo 'class="caption" title="' . htmlspecialchars($images->image_intro_caption) . '"';
								endif; ?>
								src="<?php echo $images->image_intro; ?>" alt="<?php echo htmlspecialchars($images->image_fulltext_alt); ?>"/> </div>
							</span>
							<?php endif; ?>
							<div class="caption">
								<?php  echo '<h3><a href="' . JRoute::_(TagsHelperRoute::getTagRoute($item->id . ':' . $item->alias)) . '">'
										. $this->escape($item->title) . '</a> </h3>';  ?>
								<?php  if ($this->params->get('all_tags_show_tag_description', 1)) : ?>
									<span class="tag-body">
										<?php echo JHtmlString::truncate($item->description, $this->params->get('tag_list_item_maximum_characters')); ?>
									</span>
								<?php endif; ?>
								<?php  if ($this->params->get('all_tags_show_tag_hits')) : ?>
										<span class="list-hits badge badge-info">
											<?php echo JText::sprintf('JGLOBAL_HITS_COUNT', $item->hits); ?>
										</span>
								<?php endif; ?>
							</div>
						</li>
					<?php endif;?>

				<?php if (($i == 0 && $n == 1) || $i == $n - 1 || $bscolumns == 1 || (($i + 1) % $bscolumns == 0)) :  ?>
					</ul>
				<?php endif; ?>

			<?php endforeach; ?>
<?php endif;?>
