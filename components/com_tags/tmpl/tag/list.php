<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

// Note that there are certain parts of this layout used only when there is exactly one tag.
$n    = count($this->items);
$htag = $this->params->get('show_page_heading') ? 'h2' : 'h1';

?>

<div class="com-tags-tag-list tag-category">

    <?php if ($this->params->get('show_page_heading')) : ?>
        <h1>
            <?php echo $this->escape($this->params->get('page_heading')); ?>
        </h1>
    <?php endif; ?>

    <?php if ($this->params->get('show_tag_title', 1)) : ?>
        <<?php echo $htag; ?>>
            <?php echo HTMLHelper::_('content.prepare', $this->tags_title, '', 'com_tags.tag'); ?>
        </<?php echo $htag; ?>>
    <?php endif; ?>

    <?php // We only show a tag description if there is a single tag. ?>
    <?php if (count($this->item) === 1 && ($this->params->get('tag_list_show_tag_image', 1) || $this->params->get('tag_list_show_tag_description', 1))) : ?>
        <div class="com-tags-tag-list__description category-desc">
            <?php $images = json_decode($this->item[0]->images); ?>
            <?php if ($this->params->get('tag_list_show_tag_image', 1) == 1 && !empty($images->image_fulltext)) : ?>
                <?php echo HTMLHelper::_('image', $images->image_fulltext, ''); ?>
            <?php endif; ?>
            <?php if ($this->params->get('tag_list_show_tag_description') == 1 && $this->item[0]->description) : ?>
                <?php echo HTMLHelper::_('content.prepare', $this->item[0]->description, '', 'com_tags.tag'); ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php // If there are multiple tags and a description or image has been supplied use that. ?>
    <?php if ($this->params->get('tag_list_show_tag_description', 1) || $this->params->get('show_description_image', 1)) : ?>
        <?php if ($this->params->get('show_description_image', 1) == 1 && $this->params->get('tag_list_image')) : ?>
            <?php echo HTMLHelper::_('image', $this->params->get('tag_list_image'), empty($this->params->get('tag_list_image_alt')) && empty($this->params->get('tag_list_image_alt_empty')) ? false : $this->params->get('tag_list_image_alt')); ?>
        <?php endif; ?>
        <?php if ($this->params->get('tag_list_description', '') > '') : ?>
            <?php echo HTMLHelper::_('content.prepare', $this->params->get('tag_list_description'), '', 'com_tags.tag'); ?>
        <?php endif; ?>
    <?php endif; ?>
    <?php echo $this->loadTemplate('items'); ?>
</div>
