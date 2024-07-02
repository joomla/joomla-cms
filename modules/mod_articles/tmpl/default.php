<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_articles
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->registerAndUseStyle('mod_articles', 'mod_articles/mod-articles.css');

if ($params->get('articles_layout') == 1) {
    $gridCols = $params->get('layout_columns');
    $wa->addInlineStyle(".mod-articles-grid {
        --grid-column-count: $gridCols;
    }");
}

if (!$list) {
    return;
}

?>

<?php if ($params->get('title_only', 1)) : ?>
    <?php if ($grouped) : ?>
        <?php foreach ($list as $groupName => $items) : ?>
            <div class="mod-articles-group">
                <h4><?php echo Text::_($groupName); ?></h4>
                <ul class="mod-articles mod-list">
                    <?php foreach ($items as $item) : ?>
                        <li itemscope itemtype="https://schema.org/Article">
                            <a href="<?php echo $item->link; ?>" itemprop="url">
                                <span itemprop="name">
                                    <?php echo $item->title; ?>
                                </span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <?php $items = $list; ?>
        <ul class="mod-articles mod-list">
            <?php foreach ($items as $item) : ?>
                <li itemscope itemtype="https://schema.org/Article">
                    <a href="<?php echo $item->link; ?>" itemprop="url">
                        <span itemprop="name">
                            <?php echo $item->title; ?>
                        </span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php else : ?>
    <?php if ($grouped) : ?>
        <?php foreach ($list as $groupName => $items) : ?>
            <div class="mod-articles-group">
                <h4><?php echo Text::_($groupName); ?></h4>
                <?php require ModuleHelper::getLayoutPath('mod_articles', '_items'); ?>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <?php $items = $list; ?>
        <?php require ModuleHelper::getLayoutPath('mod_articles', '_items'); ?>
    <?php endif; ?>
<?php endif; ?>
