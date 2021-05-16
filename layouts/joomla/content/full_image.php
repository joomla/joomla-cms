<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
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

$imgclass  = empty($images->float_fulltext) ? $params->get('float_fulltext') : $images->float_fulltext;
$extraAttr = '';
$img       = HTMLHelper::cleanImageURL($images->image_fulltext);
$alt       = empty($images->image_fulltext_alt) && empty($images->image_fulltext_alt_empty) ? '' : 'alt="' . htmlspecialchars($images->image_fulltext_alt, ENT_COMPAT, 'UTF-8') . '"';

// Set lazyloading only for images which have width and height attributes
if ((isset($img->attributes['width']) && (int) $img->attributes['width'] > 0)
&& (isset($img->attributes['height']) && (int) $img->attributes['height'] > 0))
{
	$extraAttr = ArrayHelper::toString($img->attributes) . ' loading="lazy"';
}
?>
<figure class="<?php echo htmlspecialchars($imgclass, ENT_COMPAT, 'UTF-8'); ?> item-image">
	<img src="<?php echo htmlspecialchars($img->url, ENT_COMPAT, 'UTF-8'); ?>"
			 <?php echo $alt; ?>
			 itemprop="image"
			<?php echo $extraAttr; ?>
	/>
	<?php if ($images->image_fulltext_caption !== '') : ?>
		<figcaption class="caption"><?php echo htmlspecialchars($images->image_fulltext_caption, ENT_COMPAT, 'UTF-8'); ?></figcaption>
	<?php endif; ?>
</figure>
