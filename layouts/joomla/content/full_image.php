<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Utilities\ArrayHelper;

$params  = $displayData->params;
$images  = json_decode($displayData->images);

if (empty($images->image_fulltext))
{
	return;
}

$imgfloat  = empty($images->float_fulltext) ? $params->get('float_fulltext') : $images->float_fulltext;
$extraAttr = '';
$img       = HTMLHelper::cleanImageURL($images->image_fulltext);

// Set lazyloading only for images that there's a record of width and height
if ((isset($img->attributes['width']) && (int) $img->attributes['width'] > 0)
&& (isset($img->attributes['width']) && (int) $img->attributes['width'] > 0))
{
	$extraAttr = ArrayHelper::toString($img->attributes) . ' loading="lazy"';
}
?>
<figure class="float-<?php echo htmlspecialchars($imgfloat, ENT_COMPAT, 'UTF-8'); ?> item-image">
	<img src="<?php echo htmlspecialchars($img->url, ENT_COMPAT, 'UTF-8'); ?>"
			 alt="<?php echo htmlspecialchars($images->image_fulltext_alt, ENT_COMPAT, 'UTF-8'); ?>"
			 itemprop="image"
			<?php echo $extraAttr; ?>
	/>
	<?php if ($images->image_fulltext_caption !== '') : ?>
		<figcaption class="caption"><?php echo htmlspecialchars($images->image_fulltext_caption, ENT_COMPAT, 'UTF-8'); ?></figcaption>
	<?php endif; ?>
</figure>
