<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_stats_admin
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<ul class="list-group list-group-flush stats-module<?php echo $moduleclass_sfx ?>">
	<?php foreach ($list as $item) : ?>
		<li class="list-group-item"><span class="mr-2 icon-<?php echo $item->icon; ?>" title="<?php echo $item->title; ?>"></span> <?php echo $item->title; ?> <?php echo $item->data; ?></li>
	<?php endforeach; ?>
</ul>
