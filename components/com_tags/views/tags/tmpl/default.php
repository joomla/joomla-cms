<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Note that there are certain parts of this layout used only when there is exactly one tag.

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
$description      = $this->params->get('all_tags_description');
$descriptionImage = $this->params->get('all_tags_description_image');

?>
<div class="tag-category<?php echo $this->pageclass_sfx; ?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
		<h1>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		</h1>
	<?php endif; ?>
	<?php if ($this->params->get('all_tags_show_description_image') && !empty($descriptionImage)) : ?>
		<div>
			<img src="<?php echo $descriptionImage; ?>" />
		</div>
	<?php endif; ?>
	<?php if (!empty($description)) : ?>
		<div>
			<?php echo $description; ?>
		</div>
	<?php endif; ?>
	<?php echo $this->loadTemplate('items'); ?>
</div>
