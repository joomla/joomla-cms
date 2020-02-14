<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('JPATH_BASE') or die();

$params = $displayData->params;
$attribs = json_decode($displayData->attribs);

$template = JFactory::getApplication('site')->getTemplate(true);
$tplParams = $template->params;

$leading = (isset($displayData->leading) && $displayData->leading) ? 1 : 0;

if($leading)
{
	$blog_list_image = $tplParams->get('leading_blog_list_image', 'large');
}
else
{
	$blog_list_image = $tplParams->get('blog_list_image', 'thumbnail');
}

$intro_image = '';
if(isset($attribs->helix_ultimate_image) && $attribs->helix_ultimate_image != '')
{
	if($blog_list_image == 'default')
	{
		$intro_image = $attribs->helix_ultimate_image;
	}
	else
	{
		$intro_image = $attribs->helix_ultimate_image;
		$basename = basename($intro_image);
		$list_image = JPATH_ROOT . '/' . dirname($intro_image) . '/' . JFile::stripExt($basename) . '_'. $blog_list_image .'.' . JFile::getExt($basename);
		if(JFile::exists($list_image)) {
			$intro_image = JURI::root(true) . '/' . dirname($intro_image) . '/' . JFile::stripExt($basename) . '_'. $blog_list_image .'.' . JFile::getExt($basename);
		}
	}
}
?>
<?php if($intro_image) : ?>
	<?php if ($params->get('link_titles') && $params->get('access-view')) : ?>
		<a href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($displayData->slug, $displayData->catid, $displayData->language)); ?>">
		<?php endif; ?>
			<div class="article-intro-image">
				<img src="<?php echo $intro_image; ?>" alt="<?php echo htmlspecialchars($displayData->title, ENT_COMPAT, 'UTF-8'); ?>">
			</div>
		<?php if ($params->get('link_titles') && $params->get('access-view')) : ?>
		</a>
	<?php endif; ?>
<?php else: ?>

	<?php $images = json_decode($displayData->images); ?>
	<?php if (isset($images->image_intro) && !empty($images->image_intro)) : ?>
		<?php $imgfloat = empty($images->float_intro) ? $params->get('float_intro') : $images->float_intro; ?>
		<div class="article-intro-image float-<?php echo htmlspecialchars($imgfloat, ENT_COMPAT, 'UTF-8'); ?>">
			<?php if ($params->get('link_titles') && $params->get('access-view')) : ?>
				<a href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($displayData->slug, $displayData->catid, $displayData->language)); ?>"><img
					<?php if ($images->image_intro_caption) : ?>
						<?php echo 'class="caption"' . ' title="' . htmlspecialchars($images->image_intro_caption) . '"'; ?>
					<?php endif; ?>
					src="<?php echo htmlspecialchars($images->image_intro, ENT_COMPAT, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($images->image_intro_alt, ENT_COMPAT, 'UTF-8'); ?>"></a>
				<?php else : ?><img
					<?php if ($images->image_intro_caption) : ?>
						<?php echo 'class="caption"' . ' title="' . htmlspecialchars($images->image_intro_caption, ENT_COMPAT, 'UTF-8') . '"'; ?>
					<?php endif; ?>
					src="<?php echo htmlspecialchars($images->image_intro, ENT_COMPAT, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($images->image_intro_alt, ENT_COMPAT, 'UTF-8'); ?>">
				<?php endif; ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
