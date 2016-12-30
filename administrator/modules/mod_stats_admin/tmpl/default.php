<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_stats_admin
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<ul class="list-striped list-condensed stats-module<?php echo $moduleclass_sfx ?>">
	<?php foreach ($list as $item) : ?>
		<?php if(isset($item->link)) : ?>
			<li><span class="icon-<?php echo $item->icon; ?>" title="<?php echo $item->title; ?>"></span> <?php echo $item->title . ' '; ?><a class="badge badge-info" href ="<?php echo $item->link; ?>"><?php echo $item->data; ?></a></li>
		<?php else : ?>
			<li><span class="icon-<?php echo $item->icon; ?>" title="<?php echo $item->title; ?>"></span> <?php echo $item->title; ?> <?php echo $item->data; ?></li>
		<?php endif; ?>
	<?php endforeach; ?>
</ul>
