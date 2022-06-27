<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$items = $displayData;

if (!empty($items)) : ?>
    <ul class="item-associations">
        <?php foreach ($items as $id => $item) : ?>
            <?php if (is_array($item) && isset($item['link'])) : ?>
                <li>
                    <?php echo $item['link']; ?>
                </li>
            <?php elseif (isset($item->link)) : ?>
                <li>
                    <?php echo $item->link; ?>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
