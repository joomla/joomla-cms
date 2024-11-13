<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_articles
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<ul class="mod-articles mod-list">
    <?php foreach ($items as $item) : ?>
        <li itemscope itemtype="https://schema.org/Article">
            <a <?php echo $item->active ? 'class="' . $item->active . '" ' : ''; ?>href="<?php echo $item->link; ?>" itemprop="url">
                <span itemprop="name">
                    <?php echo $item->title; ?>
                </span>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
