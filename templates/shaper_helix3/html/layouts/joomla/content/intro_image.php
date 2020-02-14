<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
$tplParams 		= JFactory::getApplication()->getTemplate(true)->params;
$params  		= $displayData->params;
$attribs 		= json_decode($displayData->attribs);
$images 		= json_decode($displayData->images);
$imgsize 		= $tplParams->get('blog_list_image', 'default');
$intro_image 	= '';
$image_alt_text = '';

if(isset($attribs->spfeatured_image) && $attribs->spfeatured_image != '') {
	if($imgsize == 'default') {
		$intro_image = $attribs->spfeatured_image;
	} else {
		$intro_image = $attribs->spfeatured_image;
		$basename = basename($intro_image);
		$list_image = JPATH_ROOT . '/' . dirname($intro_image) . '/' . JFile::stripExt($basename) . '_'. $imgsize .'.' . JFile::getExt($basename);
		if(file_exists($list_image)) {
			$intro_image = JURI::root(true) . '/' . dirname($intro_image) . '/' . JFile::stripExt($basename) . '_'. $imgsize .'.' . JFile::getExt($basename);
		}
	}

	if(isset($attribs->spfeatured_image_alt) && $attribs->spfeatured_image_alt) {
		$image_alt_text = $attribs->spfeatured_image_alt;
	}
} elseif(isset($images->image_intro) && !empty($images->image_intro)) {
	$intro_image = $images->image_intro;
}

// if alt text is empty 
if(empty($image_alt_text)){
	if ($images->image_intro_alt) {
		$image_alt_text = $images->image_intro_alt;
	} else {
		$image_alt_text = $displayData->title;
	}
}
?>

<?php if(!empty($intro_image) || (isset($images->image_intro) && !empty($images->image_intro))) { ?>
<?php $imgfloat = empty($images->float_intro) ? $params->get('float_intro') : $images->float_intro; ?>
<div class="pull-<?php echo htmlspecialchars($imgfloat, ENT_COMPAT, 'UTF-8'); ?> entry-image intro-image">
	<?php if ($params->get('link_titles') && $params->get('access-view')) { ?>
		<a href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($displayData->slug, $displayData->catid, $displayData->language)); ?>">
	<?php } ?>
	<img
		<?php if ($images->image_intro_caption):
			echo 'class="caption"' . ' title="' . htmlspecialchars($images->image_intro_caption) . '"';
		endif; ?>
		src="<?php echo htmlspecialchars($intro_image); ?>" alt="<?php echo htmlspecialchars($image_alt_text); ?>" itemprop="thumbnailUrl"
	/>
	<?php if ($params->get('link_titles') && $params->get('access-view')) { ?>
		</a>
	<?php } ?>
</div>
<?php } ?>
