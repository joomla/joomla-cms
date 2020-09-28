<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.cassiopeia
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

$module  = $displayData['module'];
$params  = $displayData['params'];
$attribs = $displayData['attribs'];

if ($module->content === null || $module->content === '')
{
	return;
}

$moduleTag              = $params->get('module_tag', 'div');
$moduleAttribs['class'] = $module->position . ' card ' . htmlspecialchars($params->get('moduleclass_sfx'), ENT_QUOTES, 'UTF-8');
$modId                  = 'mod-' . $module->id;
$headerTag              = htmlspecialchars($params->get('header_tag', 'h3'), ENT_QUOTES, 'UTF-8');
$headerClass            = htmlspecialchars($params->get('header_class', ''), ENT_QUOTES, 'UTF-8');

if ($module->showtitle) : ?>
	<<?php echo $moduleTag; ?> class="<?php echo $moduleAttribs['class'] ?>" aria-labelledby="<?php echo $modId; ?>">
		<<?php echo $headerTag; ?> id="<?php echo $modId; ?>" class="card-header <?php echo $headerClass; ?>"><?php echo $module->title; ?></<?php echo $headerTag; ?>>
		<div class="card-body"><?php echo $module->content; ?></div>
	</<?php echo $moduleTag; ?>>
<?php else : ?>
	<<?php echo $moduleTag; ?> id="<?php echo $modId; ?>" class="<?php echo $moduleAttribs['class'] ?>" aria-label="<?php echo $module->title; ?>">
		<div class="card-body"><?php echo $module->content; ?></div>
	</<?php echo $moduleTag; ?>>
<?php endif; ?>
