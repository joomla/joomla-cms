<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_articles
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

$currentDate = Factory::getDate()->format('Y-m-d H:i:s');

?>
<ul class="mod-articles mod-list">
    <?php foreach ($items as $item) : ?>
        <li itemscope itemtype="https://schema.org/Article">
            <a <?php echo $item->active ? 'class="' . $item->active . '" ' : ''; ?>href="<?php echo $item->link; ?>" itemprop="url">
                <span itemprop="name">
                    <?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
                </span>
            </a>

            <?php if ($item->state == 0) : ?>
                <span class="badge bg-warning"><?php echo Text::_('JUNPUBLISHED'); ?></span>
            <?php endif; ?>

            <?php if ($item->publish_up > $currentDate) : ?>
                <span class="badge bg-warning"><?php echo Text::_('JNOTPUBLISHEDYET'); ?></span>
            <?php endif; ?>

            <?php if ($item->publish_down !== null && $item->publish_down < $currentDate) : ?>
                <span class="badge bg-warning"><?php echo Text::_('JEXPIRED'); ?></span>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>
