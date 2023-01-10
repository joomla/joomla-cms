<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_archive
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var  \stdClass[]  $months  Archived articles by months */
if (!$months) {
    return;
}

?>
<ul class="mod-articlesarchive archive-module mod-list">
    <?php foreach ($months as $month) : ?>
    <li>
        <a href="<?php echo $month->link; ?>">
            <?php echo $month->name; ?> (<?php echo $month->numarticles; ?>)
        </a>
    </li>
    <?php endforeach; ?>
</ul>
