<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$class = 'pagetitle';
if (!empty($displayData['icon']))
{
	// Strip the extension.
	$icons = explode(' ', $displayData['icon']);

	foreach ($icons as $i => $icon)
	{
		$icons[$i] = 'icon-48-' . preg_replace('#\.[^.]*$#', '', $icon);
	}
	$class .= ' ' . htmlspecialchars(implode(' ', $icons));
}
?>
<div class="<?php echo $class; ?>">
	<h2>
		<?php echo $displayData['title']; ?>
	</h2>
</div>
