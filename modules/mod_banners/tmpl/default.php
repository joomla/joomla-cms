<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_banners
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Banners\Site\Helper\BannerHelper;

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
            <?php $imageurl = $item->params->get('imageurl'); ?>
            <?php if (BannerHelper::isImage($imageurl)) : ?>
                <?php // Image based banner ?>
                <?php $baseurl = strpos($imageurl, 'http') === 0 ? '' : Uri::base(); ?>
                <?php $alt = $item->params->get('alt'); ?>
                <?php $alt = $alt ?: $item->name; ?>
                <?php $alt = $alt ?: Text::_('MOD_BANNERS_BANNER'); ?>
                <?php $imageAttributes = [
                    'src' => $baseurl . $imageurl,
                    'alt' => $alt
                ]; ?>
                <?php if (!empty($item->params->get('width'))) : ?>
                    <?php $imageAttributes['width'] = $item->params->get('width'); ?>
                <?php endif; ?>
                <?php if (!empty($item->params->get('height'))) : ?>
                    <?php $imageAttributes['height'] = $item->params->get('height'); ?>
                <?php endif; ?>
                <?php $image = LayoutHelper::render('joomla.html.image', $imageAttributes); ?>
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
