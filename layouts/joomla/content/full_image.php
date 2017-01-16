<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;
$params = $displayData->params;
$images = json_decode($displayData->images);
?>
<?php if (isset($images->image_fulltext) && !empty($images->image_fulltext)) : ?>
	<?php $imgfloat = empty($images->float_fulltext) ? $params->get('float_fulltext') : $images->float_fulltext; ?>
	<div class="pull-<?php echo htmlspecialchars($imgfloat); ?> item-image">
	<?php $linkIsSet = isset($images->image_fulltext_link) && ($link = json_decode($images->image_fulltext_link, true)) && isset($link['url']); ?>
	<?php if ($linkIsSet) : ?>
		<a <?php echo 'title="' . htmlspecialchars($link['title']) . '" href="' . htmlspecialchars($link['url']) . '" rel="' . htmlspecialchars($link['rel']) . '"'; ?>>
	<?php endif; ?>
	<img
	<?php if ($images->image_fulltext_caption) :
		echo 'class="caption"' . ' title="' . htmlspecialchars($images->image_fulltext_caption) . '"';
	endif; ?>
	src="<?php echo htmlspecialchars($images->image_fulltext); ?>" alt="<?php echo htmlspecialchars($images->image_fulltext_alt); ?>" itemprop="image"/>
	<?php if ($linkIsSet) : ?>
		</a>
	<?php endif; ?>
	</div>
<?php endif; ?>
