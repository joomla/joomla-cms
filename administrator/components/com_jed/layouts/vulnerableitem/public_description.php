<?php

/**
 * @package           JED
 *
 * @subpackage        VEL
 *
 * @copyright     (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license           GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to $displayData file
// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
// phpcs:enable PSR1.Files.SideEffects

/** @var $displayData array */
$headerlabeloptions = ['hiddenLabel' => true];
$fieldhiddenoptions = ['hidden' => true];
?>

<div class="row vel-header-row">
    <div class="col-md-12 vel-header">
        <h1>Public Description
            <button type="button" class="" id="buildPublicDescription">
                <span class="icon-wand"></span>
            </button>
        </h1>
        <?php echo $displayData->renderField('public_description', null, null, $headerlabeloptions); ?>
    </div>
</div>
<div class="row vel-header-row">
    <div class="col-md-12 vel-header">
        <h1>Alias
            <button type="button" class="" id="buildAlias">
                <span class="icon-wand"></span>
            </button>
        </h1>
        <?php echo $displayData->renderField('alias', null, null, $headerlabeloptions); ?>
    </div>
</div>
   

