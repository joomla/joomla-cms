<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.cassiopeia
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$module  = $displayData['module'];
$params  = $displayData['params'];
$attribs = $displayData['attribs'];

$modulePos   = $module->position;
$moduleTag   = $params->get('module_tag', 'div');
$headerTag   = htmlspecialchars($params->get('header_tag', 'h4'));
$headerClass = htmlspecialchars($params->get('header_class', ''));

if ($module->content) : ?>
	<<?php echo $moduleTag; ?> class="<?php echo $modulePos; ?> card <?php echo htmlspecialchars($params->get('moduleclass_sfx')); ?>">
		<?php if ($module->showtitle && $headerClass !== 'card-title') : ?>
			<<?php echo $headerTag; ?> class="card-header<?php echo $headerClass; ?>"><?php echo $module->title; ?></<?php echo $headerTag; ?>>
		<?php endif; ?>
		<div class="card-body">
			<?php if ($module->showtitle && $headerClass === 'card-title') : ?>
				<<?php echo $headerTag; ?> class="<?php echo $headerClass; ?>"><?php echo $module->title; ?></<?php echo $headerTag; ?>>
			<?php endif; ?>
			<?php echo $module->content; ?>
		</div>
	</<?php echo $moduleTag; ?>>
<?php endif; ?>
