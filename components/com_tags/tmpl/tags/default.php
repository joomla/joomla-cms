<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

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
		<?php $alt = empty($this->params->get('all_tags_description_image_alt')) && empty($this->params->get('all_tags_description_image_alt_empty'))
			? ''
			: 'alt="' . htmlspecialchars($this->params->get('all_tags_description_image_alt'), ENT_COMPAT, 'UTF-8') . '"'; ?>
		<div class="com-tags__image">
			<img src="<?php echo htmlspecialchars($descriptionImage, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $alt; ?>>
		</div>
	<?php endif; ?>
	<?php if (!empty($description)) : ?>
		<div class="com-tags__description">
			<?php echo $description; ?>
		</div>
	<?php endif; ?>
	<?php echo $this->loadTemplate('items'); ?>
</div>
