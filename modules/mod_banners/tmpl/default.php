<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_banners
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\MediaHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
?>
<div class="mod-banners bannergroup">
<?php if ($headerText) : ?>
    <div class="bannerheader">
        <?php echo $headerText; ?>
    </div>
<?php endif; ?>

<?php foreach ($list as $item) : ?>
    <div class="mod-banners__item banneritem">
        <?php $link = Route::_('index.php?option=com_banners&task=click&id=' . $item->id); ?>
        <?php if ($item->type == 1) : ?>
            <?php // Text based banners ?>
            <?php echo str_replace(['{CLICKURL}', '{NAME}'], [$link, $item->name], $item->custombannercode); ?>
        <?php else : ?>
            <?php $imageobject = HTMLHelper::cleanImageURL($item->params->get('imageurl')); ?>
            <?php $imageurl = $imageobject->url; ?>
            <?php if (!empty($imageurl) && (MediaHelper::isImage($imageurl) || MediaHelper::getMimeType($imageurl) === 'image/svg+xml')) : ?>
                <?php // Image based banner ?>
                <?php $alt = $item->params->get('alt'); ?>
                <?php $alt = $alt ?: $item->name; ?>
                <?php $alt = $alt ?: Text::_('MOD_BANNERS_BANNER'); ?>
                <?php $width = $item->params->get('width'); ?>
                <?php $height = $item->params->get('height'); ?>
                <?php $imageAttribs = [
                    'src' => strpos($imageurl, 'http') === 0 ? $imageurl : HTMLHelper::_('image', $imageurl, '', null, false, 1),
                    'alt' => $alt
                ];?>
                <?php if (!empty($width)) : ?>
                    <?php $imageAttribs['width'] = $width; ?>
                <?php endif; ?>
                <?php if (!empty($height)) : ?>
                    <?php $imageAttribs['height'] = $height; ?>
                <?php endif; ?>
                <?php if ($item->params->get('responsive_imageurls', [])) : ?>
                    <?php $srcset = [$imageAttribs['src'] . ' ' . (int) $imageobject->attributes['width'] . 'w']; ?>
                    <?php foreach ((array) $item->params->get('responsive_imageurls', []) as $responsiveImage) : ?>
                        <?php if (!empty($responsiveImage->imageurl)) : ?>
                            <?php $responsiveImageObject = HTMLHelper::_('cleanImageURL', $responsiveImage->imageurl); ?>
                            <?php if (empty($responsiveImageObject->attributes['width'])) : ?>
                                <?php continue; ?>
                            <?php endif; ?>
                            <?php $srcset[] = HTMLHelper::_('image', $responsiveImageObject->url, '', null, false, 1) . ' ' . (int) $responsiveImageObject->attributes['width'] . 'w'; ?>
                            <?php endif; ?>
                    <?php endforeach; ?>
                    <?php $sizes = []; ?>
                    <?php if ($item->params->get('sizes', [])) : ?>
                        <?php foreach ((array) $item->params->get('sizes', []) as $size) : ?>
                            <?php if (!empty($size->mediaquery) && !empty($size->container_width)) : ?>
                                <?php $sizes[] = '(min-width: ' . (int) $size->mediaquery . 'px) ' . (int) $size->container_width . $size->widthunit; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php $sizes[] = '100vw'; ?>
                    <?php $imageAttribs['srcset'] = implode(', ', $srcset); ?>
                    <?php $imageAttribs['sizes'] = implode(', ', $sizes); ?>
                <?php endif; ?>
                <?php $image = LayoutHelper::render('joomla.html.image', $imageAttribs); ?>
                <?php if ($item->clickurl) : ?>
                    <?php // Wrap the banner in a link ?>
                    <?php $target = $params->get('target', 1); ?>
                    <?php if ($target == 1) : ?>
                        <?php // Open in a new window ?>
                        <a
                            href="<?php echo $link; ?>" target="_blank" rel="noopener noreferrer"
                            title="<?php echo htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo $image; ?>
                        </a>
                    <?php elseif ($target == 2) : ?>
                        <?php // Open in a popup window ?>
                        <a
                            href="<?php echo $link; ?>" onclick="window.open(this.href, '',
                                'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=780,height=550');
                                return false"
                            title="<?php echo htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo $image; ?>
                        </a>
                    <?php else : ?>
                        <?php // Open in parent window ?>
                        <a
                            href="<?php echo $link; ?>"
                            title="<?php echo htmlspecialchars($item->name, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo $image; ?>
                        </a>
                    <?php endif; ?>
                <?php else : ?>
                    <?php // Just display the image if no link specified ?>
                    <?php echo $image; ?>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<?php if ($footerText) : ?>
    <div class="mod-banners__footer bannerfooter">
        <?php echo $footerText; ?>
    </div>
<?php endif; ?>
</div>
