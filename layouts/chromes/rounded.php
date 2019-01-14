<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Module chrome that allows for rounded corners by wrapping in nested div tags
 */

defined('_JEXEC') or die;

$module  = $displayData['module'];
$params  = $displayData['params'];
?>
<div class="module <?php echo htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8'); ?>">
	<div>
		<div>
			<div>
				<?php if ((bool) $module->showtitle) : ?>
					<h3><?php echo $module->title; ?></h3>
				<?php endif; ?>
				<?php echo $module->content; ?>
			</div>
		</div>
	</div>
</div>
