<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$params  = $displayData->params;
$images  = json_decode($displayData->images);
$caption = !empty($images->image_fulltext_caption) ? $images->image_fulltext_caption : '';
?>
<?php if (isset($images->image_fulltext) && !empty($images->image_fulltext)) : ?>
	<?php $imgfloat = empty($images->float_fulltext) ? $params->get('float_fulltext') : $images->float_fulltext; ?>
	<figure class="pull-<?php echo htmlspecialchars($imgfloat, ENT_COMPAT, 'UTF-8'); ?> item-image">
		<?php if ($caption) : ?>
			<figcaption class="caption"><?php echo htmlspecialchars($caption, ENT_COMPAT, 'UTF-8'); ?></figcaption>
		<?php endif; ?>
		<img src="<?php echo htmlspecialchars($images->image_fulltext, ENT_COMPAT, 'UTF-8'); ?>"
			 alt="<?php echo htmlspecialchars($images->image_fulltext_alt, ENT_COMPAT, 'UTF-8'); ?>"
			 itemprop="image"/>
	</figure>
<?php endif; ?>
