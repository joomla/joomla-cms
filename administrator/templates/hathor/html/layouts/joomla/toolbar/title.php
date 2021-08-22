<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
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
	$class .= ' ' . htmlspecialchars(implode(' ', $icons), ENT_COMPAT, 'UTF-8');
}
?>
<div class="<?php echo $class; ?>">
	<h2>
		<?php echo $displayData['title']; ?>
	</h2>
</div>
