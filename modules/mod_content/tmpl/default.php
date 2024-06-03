<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_content
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;

if (!$list) {
    return;
}

?>
<?php if ($params->get('title_only', 1)) : ?>
    <ul class="mod-content mod-list">
        <?php if ($grouped) : ?>
            <?php foreach ($list as $item) : ?>
                <li itemscope itemtype="https://schema.org/Article">
                    <a href="<?php echo $item->link; ?>" itemprop="url">
                        <span itemprop="name">
                            <?php echo $item->title; ?>
                        </span>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php else : ?>
            <?php foreach ($list as $item) : ?>
                <li itemscope itemtype="https://schema.org/Article">
                    <a href="<?php echo $item->link; ?>" itemprop="url">
                        <span itemprop="name">
                            <?php echo $item->title; ?>
                        </span>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
<?php else : ?>
    <div class="mod-content mod-list">
        <?php if ($grouped) : ?>
            <?php foreach ($list as $groupName => $items) : ?>
                <div class="mod-content-group">
                    <h4><?php echo Text::_($groupName); ?></h4>
                    <?php require ModuleHelper::getLayoutPath('mod_content', $params->get('layout', 'default') . '_items'); ?>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <?php $items = $list; ?>
            <?php require ModuleHelper::getLayoutPath('mod_content', $params->get('layout', 'default') . '_items'); ?>
        <?php endif; ?>
    </div>
<?php endif; ?>
