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
$description      = $this->params->get('all_tags_description');
$descriptionImage = $this->params->get('all_tags_description_image');
?>
<div class="com-tags tag-category">
	<?php if ($this->params->get('show_page_heading')) : ?>
		<h1>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		</h1>
	<?php endif; ?>
	<?php if ($this->params->get('all_tags_show_description_image') && !empty($descriptionImage)) : ?>
		<div class="com-tags__image">
			<?php echo HTMLHelper::_('image', $descriptionImage, empty($this->params->get('all_tags_description_image_alt')) && empty($this->params->get('all_tags_description_image_alt_empty')) ? false : $this->params->get('all_tags_description_image_alt')); ?>
		</div>
	<?php endif; ?>
	<?php if (!empty($description)) : ?>
		<div class="com-tags__description">
			<?php echo $description; ?>
		</div>
	<?php endif; ?>
	<?php echo $this->loadTemplate('items'); ?>
</div>
