<?php
/**
 * @subpackage  com_tags
 * @copyright   Copyright (C) 2013 Troy T. Hall All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Create a shortcut for params.
$params = $this -> params;
$item = $this -> item;
$images = json_decode($item->core_images);

// columns
$columns = 3;
$item_span = round((12 / $columns));
$targetUrl = JRoute::_(ContentHelperRoute::getArticleRoute($item -> content_item_id, $item -> core_catid));
if (isset($images -> image_intro) && !empty($images -> image_intro)) {
    $imgFloat = (empty($images -> float_intro)) ? 'right' : htmlspecialchars($images -> float_intro);
    $imageAttr = ($images -> image_intro_caption) ? 'class="caption"' . ' title="'
            . htmlspecialchars($images->image_intro_caption) . '"' : '';
    $imageAttr .= 'src="' . htmlspecialchars($images -> image_intro) . '"'
        . ' alt="' . htmlspecialchars($images -> image_intro_alt) . '"';
} else {
    $imageAttr = '';
}
?>


<div class="col-md-<?php echo $item_span; ?> portfolio-element genre" data-category="transition">
	<div class="portfolio-item">
<?php
    if ($imageAttr) :
?>
		<div class="img-intro-<?php echo $imgFloat; ?>">
			<div class="img-wrapper">
				<a href="<?php echo $targetUrl; ?>">
					<img <?php echo $imageAttr; ?>>
					<div class="image-backdrop"></div>
					<div class="img-intro-btn"></div>
				</a>
			</div>
		</div>
<?php
    endif;
?>
	<div class="page-header">
		<h3><!-- Bear --> 
			<a href="<?php echo $targetUrl; ?>"> <?php echo $this->escape($item -> core_title); ?></a>
		</h3>
	</div>
<?php if ($item -> core_state == 0) : ?>
	<span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span>
<?php endif; ?>
	</div>
</div>

