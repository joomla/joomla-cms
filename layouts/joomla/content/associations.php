<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$items = $displayData;

if (!empty($items)) : ?>
	<ul class="item-associations">
		<?php foreach ($items as $id => $item) : ?>
				<li>
					<?php echo $item->link; ?>
				</li>
		<?php endforeach; ?>
	</ul>
<?php endif;
