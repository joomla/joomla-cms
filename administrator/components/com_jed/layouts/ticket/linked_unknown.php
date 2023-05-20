<?php

/**
 * @package       JED
 *
 * @subpackage    Tickets
 *
 * @copyright     (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to file
// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
// phpcs:enable PSR1.Files.SideEffects
/* @var $displayData array */
$headerlabeloptions = ['hiddenLabel' => true];
$fieldhiddenoptions = ['hidden' => true];
//var_dump($displayData);exit();
$rawData = $displayData->getData();
?>
<div class="row">
    <div class="col">
        <div class="widget">
            <h1>Unknown</h1>
            <div class="container">
                <div class="row">
                    <p>Fields and Data here</p>
                </div>
            </div>
        </div>
    </div>
</div>
