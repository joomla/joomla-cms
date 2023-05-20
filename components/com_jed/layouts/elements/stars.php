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
 * @param   integer  $rating  The rounded average rating for an extension
 */

/** @var array $displayData */
extract($displayData);

// @TODO improve this later on to fill half stars as well
$rating = round($score / 20);
?>

<div class="stars">
    <?php for ($i = 0; $i < $rating; $i++) : ?>
        <div class="star"><span aria-hidden="true" class="icon-star"></span></div>
    <?php endfor; ?>
    <?php for ($i = 0; $i < (5 - $rating); $i++) : ?>
        <div class="star"><span aria-hidden="true" class="icon-star-empty"></span></div>
    <?php endfor; ?>
</div>
