<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_news
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->registerAndUseStyle('mod_articles_news_vertical', 'mod_articles_news/template-vert.css');

if (!$list) {
    return;
}

?>
<ul class="mod-articlesnews-vertical newsflash-vert mod-list">
    <?php for ($i = 0, $n = count($list); $i < $n; $i++) : ?>
        <?php $item = $list[$i]; ?>
        <li class="newsflash-item" itemscope itemtype="https://schema.org/Article">
            <?php require ModuleHelper::getLayoutPath('mod_articles_news', '_item'); ?>

            <?php if ($n > 1 && (($i < $n - 1) || $params->get('showLastSeparator'))) : ?>
                <span class="article-separator">&#160;</span>
            <?php endif; ?>
        </li>
    <?php endfor; ?>
</ul>
