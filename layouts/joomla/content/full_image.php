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
use Joomla\CMS\Layout\LayoutHelper;

$params  = $displayData->params;
$images  = json_decode($displayData->images);

if (empty($images->image_fulltext))
{
	return;
}

$imgclass   = empty($images->float_fulltext) ? $params->get('float_fulltext') : $images->float_fulltext;
$img        = HTMLHelper::cleanImageURL($images->image_fulltext);
$layoutAttr = [
	'src'      => $img->url,
	'itemprop' => 'image',
	'alt'      => empty($images->image_fulltext_alt) && empty($images->image_fulltext_alt_empty) ? '' : $images->image_fulltext_alt,
];

// Set lazyloading only for images which have width and height attributes
if ((isset($img->attributes['width']) && (int) $img->attributes['width'] > 0)
&& (isset($img->attributes['height']) && (int) $img->attributes['height'] > 0))
{
	$layoutAttr = array_merge($layoutAttr, $img->attributes, ['loading' => 'lazy']);
}
?>
<figure class="<?php echo htmlspecialchars($imgclass, ENT_COMPAT, 'UTF-8'); ?> item-image">
	<?php echo LayoutHelper::render('joomla.html.image', $layoutAttr); ?>
	<?php if ($images->image_fulltext_caption !== '') : ?>
		<figcaption class="caption"><?php echo htmlspecialchars($images->image_fulltext_caption, ENT_COMPAT, 'UTF-8'); ?></figcaption>
	<?php endif; ?>
</figure>
