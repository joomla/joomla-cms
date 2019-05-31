<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$items = $displayData;

if (!empty($items)) : ?>
	<ul class="item-associations">
		<?php if (array_key_first($items) === 'master') : ?>
			<li class="master-language">
				<?php echo is_array(array_values($items)[0]) ? array_values($items)[0]['link'] : array_values($items)[0]->link; ?>
				<hr>
				<ul class="target-languages">
					<?php foreach ($items as $id => $item) : ?>
						<?php if ($id !== 'master') : ?>
							<li>
								<?php echo is_array($item) ? $item['link'] : $item->link; ?>
							</li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			</li>
		<?php else : ?>
			<?php foreach ($items as $id => $item) : ?>
				<li>
					<?php echo is_array($item) ? $item['link'] : $item->link; ?>
				</li>
			<?php endforeach; ?>
		<?php endif; ?>
	</ul>
<?php endif; ?>
