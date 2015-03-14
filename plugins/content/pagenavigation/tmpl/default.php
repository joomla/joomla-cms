<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.pagenavigation
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$lang = JFactory::getLanguage(); ?>

<ul class="pager pagenav">
<?php if ($row->prev) :
	$direction = $lang->isRTL() ? 'right' : 'left'; ?>
	<li class="previous">
		<a href="<?php echo $row->prev; ?>" rel="prev">
			<?php echo '<i class="icon-chevron-' . $direction . '"></i> ' . $row->prev_label; ?>
		</a>
	</li>
<?php endif; ?>
<?php if ($row->next) :
	$direction = $lang->isRTL() ? 'left' : 'right'; ?>
	<li class="next">
		<a href="<?php echo $row->next; ?>" rel="next">
			<?php echo $row->next_label . ' <i class="icon-chevron-' . $direction . '"></i>'; ?>
		</a>
	</li>
<?php endif; ?>
</ul>
