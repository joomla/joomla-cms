<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * html5 (chosen html5 tag and font header tags)
 */

defined('_JEXEC') or die;

$module  = $displayData['module'];
$params  = $displayData['params'];

if ((string) $module->content === '')
{
	return;
}

$moduleTag     = htmlspecialchars($params->get('module_tag', 'div'), ENT_QUOTES, 'UTF-8');
$headerTag     = htmlspecialchars($params->get('header_tag', 'h3'), ENT_QUOTES, 'UTF-8');
$bootstrapSize = (int) $params->get('bootstrap_size', 0);
$moduleClass   = $bootstrapSize !== 0 ? ' col-md-' . $bootstrapSize : '';

// Temporarily store header class in variable
$headerClass = $params->get('header_class');
$headerClass = !empty($headerClass) ? ' class="' . htmlspecialchars($headerClass, ENT_COMPAT, 'UTF-8') . '"' : '';

?>
<<?php echo $moduleTag; ?> class="moduletable <?php echo htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8') . $moduleClass; ?>">
	<?php if ((bool) $module->showtitle) : ?>
		<<?php echo $headerTag . $headerClass . '>' . $module->title; ?></<?php echo $headerTag; ?>>
	<?php endif; ?>
	<?php echo $module->content; ?>
</<?php echo $moduleTag; ?>>
