<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_stats
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<ul class="list-group">
<?php foreach ($list as $item) : ?>
	<li class="list-group-item justify-content-between">
		<?php echo $item->title; ?>
		<span class="badge badge-default badge-pill"><?php echo $item->data; ?></span>
	</li>
<?php endforeach; ?>
</ul>
