<?php

/**
 * @package        JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Jed\Component\Jed\Site\Helper\JedtrophyHelper;
use Jed\Component\Jed\Site\View\Extension\HtmlView;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;

/**
 * @var array        $displayData
 */

/** @var array $displayData */

?>

<div class="jed-cards-wrapper margin-bottom-half">
    <h2 class="heading heading--m">Other extensions by PWT Extensions (3)</h2>
    <div class="jed-container">
        <ul class="jed-grid jed-grid--1-1-1">
            <?php for ($i = 0; $i < 3; $i++) : ?>
                <?= LayoutHelper::render('cards.extension', [
                    'image'         => 'https://extensionscdn.joomla.org/cache/fab_image/596c962509d22_resizeDown400px175px16.jpg',
                    'title'         => 'Akeeba Backup',
                    'developer'     => 'Akeeba Ltd',
                    'rating'        => 5,
                    'reviews'       => 1061,
                    'compatibility' => ['3', '4 alpha'],
                    'description'   => 'Akeeba Backup Core is the most widely used open-source backup component for the Joomla! CMS. Its mission is simple: create a site backup that can be restored on any Joomla!-capable server.',
                    'type'          => 'Free',
                    'category'      => 'Site Security',
                    'link'          => '#',
                ]) ?>
            <?php endfor; ?>
        </ul>
    </div>
</div>

<div class="jed-cards-wrapper margin-bottom-half">
    <div class="jed-container">
        <h2 class="heading heading--m">You might also be interested in</h2>
        <ul class="jed-grid jed-grid--1-1-1">
            <?php for ($i = 0; $i < 3; $i++) : ?>
                <?= LayoutHelper::render('cards.extension', [
                    'image'         => 'https://extensionscdn.joomla.org/cache/fab_image/596c962509d22_resizeDown400px175px16.jpg',
                    'title'         => 'Akeeba Backup',
                    'developer'     => 'Akeeba Ltd',
                    'rating'        => 5,
                    'reviews'       => 1061,
                    'compatibility' => ['3', '4 alpha'],
                    'description'   => 'Akeeba Backup Core is the most widely used open-source backup component for the Joomla! CMS. Its mission is simple: create a site backup that can be restored on any Joomla!-capable server.',
                    'type'          => 'Free',
                    'category'      => 'Site Security',
                    'link'          => '#',
                ]) ?>
            <?php endfor; ?>
        </ul>
    </div>
</div>
