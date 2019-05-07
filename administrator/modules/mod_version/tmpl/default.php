<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_version
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;


?>
<?php if (!empty($version)) :

	$versionParts = explode("-", $version);
	$versionNumber = $versionParts[0];
	$versionName   = $versionParts[1]; ?>
<diV class="header-element-content">
    <div class="joomlaversion d-flex">
        <div class="d-flex align-items-end mx-auto">
            <span class="fab fa-joomla" aria-hidden="true"></span>
        </div>
        <div class="d-flex align-items-center tiny mx-auto">
            <?php echo $versionName; ?>
        </div>
        <span class="badge badge-pill badge-success"><?php echo $versionNumber; ?></span>
    </div>
</diV>
<?php endif; ?>
