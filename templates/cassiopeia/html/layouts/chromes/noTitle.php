<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.crocosmia
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$module  = $displayData['module'];
$params  = $displayData['params'];
$attribs = $displayData['attribs'];

$modulePos = $module->position;
$moduleTag = $params->get('module_tag', 'div');
$modId     = 'mod-' . $module->id;

if ($module->content) : ?>
	<div id="<?php echo $modId; ?>" class="<?php echo $modulePos; ?> <?php echo htmlspecialchars($params->get('moduleclass_sfx')); ?>"  aria-labelledby="<?php echo $module->title; ?>">
		<div>
		<?php echo $module->content; ?>
		</div>
	</<?php echo $moduleTag; ?>>
<?php endif; ?>
