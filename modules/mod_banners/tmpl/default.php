<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_banners
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once JPATH_ROOT . '/components/com_banners/helpers/banner.php';
$baseurl = JURI::base();
?>
<div class="bannergroup<?php echo $params->get('moduleclass_sfx') ?>">
<?php if ($headerText) : ?>
	<h3><?php echo $headerText; ?></h3>
<?php endif; ?>

<?php foreach($list as $item):?>
	<div class="banneritem">
		<?php $link = JRoute::_('index.php?option=com_banners&task=click&id='. $item->id);?>
		<?php if($item->type==1) :?>
			<?php // Text based banners ?>
			<?php echo str_replace(array('{CLICKURL}', '{NAME}'), array($link, $item->name), $item->params->custom->bannercode);?>
		<?php else:?>
			<?php $imageurl = $item->params->image->url;?>
			<?php if (BannerHelper::isImage($imageurl)) :?>
				<?php // Image based banner ?>
				<?php $alt = $item->params->alt->alt;?>
				<?php $alt = $alt ? $alt : $item->name ;?>
				<?php $alt = $alt ? $alt : JText::_('mod_banners_Banner') ;?>
				<?php if ($item->clickurl) :?>
					<?php // Wrap the banner in a link?>
					<?php $target = $params->get('target', 1);?>
					<?php if ($target == 1) :?>
						<?php // Open in a new window?>
						<a
							href="<?php echo $link; ?>" target="_blank"
							title="<?php echo htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8');?>">
							<img
								src="<?php echo $baseurl;?>images/banners/<?php echo $imageurl;?>"
								alt="<?php echo $alt;?>" />
						</a>
					<?php elseif ($target == 2):?>
						<?php // open in a popup window?>
						<a
							href="javascript:void window.open('<?php echo $link;?>', '',
								'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=780,height=550');
								return false"
							title="<?php echo htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8');?>">
							<img
								src="<?php echo $baseurl;?>images/banners/<?php echo $imageurl;?>"
								alt="<?php echo $alt;?>" />
						</a>
					<?php else :?>
						<?php // open in parent window?>
						<a
							href="<?php echo $link;?>"
							title="<?php echo htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8');?>">
							<img
								src="<?php echo $baseurl;?>images/banners/<?php echo $imageurl;?>"
								alt="<?php echo $alt;?>" />
						</a>
					<?php endif;?>
				<?php else :?>
					<?php // Just display the image if no link specified?>
					<img
						src="<?php echo $baseurl;?>images/banners/<?php echo $imageurl;?>"
						alt="<?php echo $alt;?>" />
				<?php endif;?>
			<?php elseif (BannerHelper::isFlash($imageurl)) :?>
				<object
					classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
					codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0"
					border="0"
					width="<?php echo $parameters->flash->width;?>"
					height="<?php echo $parameters->flash->height;?>"
				>
					<param name="movie" value="<?php echo $imageurl;?>" />
					<embed
						src="<?php echo $imageurl;?>"
						loop="false"
						pluginspage="http://www.macromedia.com/go/get/flashplayer"
						type="application/x-shockwave-flash"
						width="<?php echo $parameters->flash->width;?>"
						height="<?php echo $parameters->flash->height;?>"
					></embed>
				</object>
			<?php endif;?>
		<?php endif;?>
		<div class="clr"></div>
	</div>
<?php endforeach; ?>

<?php if ($footerText) : ?>
	<div class="bannerfooter">
		<?php echo $footerText; ?>
	</div>
<?php endif; ?>
</div>

