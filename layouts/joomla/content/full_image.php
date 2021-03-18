<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$params  = $displayData->params;
$images  = json_decode($displayData->images);
?>
<?php if (!empty($images->image_fulltext)) : ?>
	<?php $imgfloat = empty($images->float_fulltext) ? $params->get('float_fulltext') : $images->float_fulltext; ?>
	<figure class="float-<?php echo htmlspecialchars($imgfloat, ENT_COMPAT, 'UTF-8'); ?> item-image">
		<img loading="lazy" src="<?php echo htmlspecialchars($images->image_fulltext, ENT_COMPAT, 'UTF-8'); ?>"
			 alt="<?php echo htmlspecialchars($images->image_fulltext_alt, ENT_COMPAT, 'UTF-8'); ?>"
			 itemprop="image"/>
		<?php if ($images->image_fulltext_caption !== '') : ?>
			<figcaption class="caption"><?php echo htmlspecialchars($images->image_fulltext_caption, ENT_COMPAT, 'UTF-8'); ?></figcaption>
		<?php endif; ?>
	</figure>
<?php endif; ?>
