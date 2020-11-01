<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   string  $text  infotext to be displayed
 */

// Closing the opening .control-group and .control-label div so we can add our info text on own line ?>
</div></div>
<div class="controls"><?php echo $text; ?></div>
<?php // Creating new .control-group and .control-label for the actual field ?>
<div class="control-group"><div class="control-label">
