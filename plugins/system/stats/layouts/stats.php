<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Stats
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var  array  $statsData  Array containing the data that will be sent to the stats server
 */
?>
<dl class="dl-horizontal js-pstats-data-details"  style="display:none;">
	<?php foreach ($statsData as $key => $value) : ?>
		<dt><?php echo $key; ?></dt>
		<dd><?php echo $value; ?></dd>
	<?php endforeach; ?>
</dl>
