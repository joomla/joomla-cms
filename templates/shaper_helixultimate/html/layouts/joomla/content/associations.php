<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('JPATH_BASE') or die();

$items = $displayData;

if (!empty($items)) : ?>
	<ul class="item-associations">
		<?php foreach ($items as $id => $item) : ?>
			<li>
				<?php echo is_array($item) ? $item['link'] : $item->link; ?>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
