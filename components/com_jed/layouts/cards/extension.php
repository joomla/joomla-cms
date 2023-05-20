<?php

/**
 * @package    JED
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @param   string   $image          The url of the image location
 * @param   string   $title          The title of the extension
 * @param   string   $developer      The name of the developer
 * @param   integer  $score          The score of the extension
 * @param   integer  $reviews        The number of reviews for the extension
 * @param   array    $compatibility  The compatible Joomla versions for the extensions
 * @param   string   $description    A short description of the extension
 * @param   string   $type           The extension type (free/paid/cloud)
 * @param   bool     $category       The main category of the extension
 * @param   string   $link           The link to the extension
 */

/** @var array $displayData */
extract($displayData);

?>

<li class="jed-grid__item">
    <div class="card card--extension">
        <div class="card__image">
            <div class="image-placeholder">
                <?php if ($image) : ?>
                    <img src="<?php echo $image; ?>" alt="<?php echo $title; ?>"/>
                <?php endif; ?>
            </div>
        </div>
        <div class="card__header">
            <a href="<?php echo $link; ?>" class="card__extension-title"><?php echo $title; ?></a>
            <div class="card__extension-developer">By <?php echo $developer; ?></div>
            <div class="align-boxes">
                <div class="stars-wrapper">
                    <?php echo $score_string; ?>
                    <?php echo $reviews; ?>
                </div>
                <?php if ($compatibility) : ?>
                    <div class="compatibility-wrapper">
                        <?php /*foreach ($compatibility as $version): ?>
                            <div class="compatible-with-joomla<?php echo $version; ?>">
                                <span class="visually-hidden">Joomla</span>
                                <span aria-hidden="true" class="icon-joomla"></span> <?php echo $version; ?>
                            </div>
                        <?php endforeach; */ echo $compatibility; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="card__description">
            <?php echo $description; ?>
        </div>
        <div class="card__footer">
            <div class="card__extension-type"><?php echo $type; ?></div>
            <div class="card__extension-category"><?php echo $category; ?></div>
        </div>
    </div>
</li>
