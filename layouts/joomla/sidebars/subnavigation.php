<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\Filter\OutputFilter;

/**
 * @var array $displayData
 * @var array $entries
 */
$entries = array_key_exists('entries', $displayData) ? (array) $displayData['entries'] : [];
?>
<?php if (count($entries) > 0) { ?>
<div id="js-subnavigation">
    <nav class="navbar navbar-expand-lg navbar-light bg-light mb-1">
        <div class="navbar-nav">
            <?php foreach ($entries as $item) { ?>
                <a class="nav-item nav-link <?php echo $item[2] ? 'active' : ''; ?>" href="<?php echo OutputFilter::ampReplace($item[1]); ?>">
                    <?php echo $item[0]; ?>
                </a>
            <?php } ?>
        </div>
    </nav>
</div>
<?php } ?>