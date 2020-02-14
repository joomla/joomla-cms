<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
$params  = $displayData->params;
$attribs 		= json_decode($displayData->attribs);
$images 		= json_decode($displayData->images);
$full_image 	= '';

if(isset($attribs->spfeatured_image) && $attribs->spfeatured_image != '') {
	$full_image = $attribs->spfeatured_image;
} elseif(isset($images->image_fulltext) && !empty($images->image_fulltext)) {
	$full_image = $images->image_fulltext;
}
?>

<?php if(!empty($full_image) || (isset($images->image_fulltext) && !empty($images->image_fulltext))) { ?>
	<?php $imgfloat = (empty($images->float_fulltext)) ? $params->get('float_fulltext') : $images->float_fulltext; ?>
	<div class="pull-<?php echo htmlspecialchars($imgfloat); ?> entry-image full-image">
		<img
		<?php if ($images->image_fulltext_caption):
		echo 'class="caption"' . ' title="' . htmlspecialchars($images->image_fulltext_caption) . '"';
		endif; ?>
		src="<?php echo htmlspecialchars($full_image); ?>" alt="<?php echo htmlspecialchars($images->image_fulltext_alt); ?>" itemprop="image"/>
	</div>
<?php } ?>
