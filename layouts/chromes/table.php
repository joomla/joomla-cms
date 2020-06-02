<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Module chrome that wraps the module in a table
 */
defined('_JEXEC') or die;

$module  = $displayData['module'];
$params  = $displayData['params'];
?>
<table cellpadding="0" cellspacing="0"
	class="moduletable <?php echo htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8'); ?>">
	<?php if ((bool) $module->showtitle) : ?>
		<tr>
			<th>
				<?php echo $module->title; ?>
			</th>
		</tr>
	<?php endif; ?>
	<tr>
		<td>
			<?php echo $module->content; ?>
		</td>
	</tr>
</table>
