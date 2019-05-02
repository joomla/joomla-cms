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

<div class="d-flex align-items-center joomlaversion"><span class="badge badge-pill badge-success"><?php echo $versionNumber; ?></span>
	<p class="joomla-version w-100 text-center"><br><?php echo $versionName; ?></p>
</div>
<?php endif; ?>
