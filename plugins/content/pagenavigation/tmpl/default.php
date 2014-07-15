<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.pagenavigation
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<ul class="pager pagenav">
<?php if ($row->prev) : ?>
	<li class="previous">
		<a href="<?php echo $row->prev; ?>" rel="prev">
			<?php echo '<i class="icon-chevron-left"></i> ' . $row->prev_label; ?>
		</a>
	</li>
<?php endif; ?>
<?php if ($row->next) : ?>
	<li class="next">
		<a href="<?php echo $row->next; ?>" rel="next">
			<?php echo $row->next_label . ' <i class="icon-chevron-right"></i>'; ?>
		</a>
	</li>
<?php endif; ?>
</ul>
