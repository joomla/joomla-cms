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
$modulePos 		= $module->position;
$moduleAttribs['class'] = $module->position . ' card card-grey ' . htmlspecialchars($params->get('moduleclass_sfx'), ENT_QUOTES, 'UTF-8');
$modId                 = 'mod-' . $module->id;
$headerTag              = htmlspecialchars($params->get('header_tag', 'h4'), ENT_QUOTES, 'UTF-8');
$headerClass            = htmlspecialchars($params->get('header_class', ''), ENT_QUOTES, 'UTF-8');

$header = '<div class="card-header'. $headerClass .'"><h3>' . $module->title . '</h3/></div>';
if ($module->content) : ?>
	<?php if ($module->showtitle) : ?>
		<div id="<?php echo $modId; ?>" class="<?php echo $moduleAttribs['class'] ?>">
			<?php echo $header; ?>
			<div class="card-body"><?php echo $module->content; ?></div>
		</div>
	<?php else : ?>
		<div id="<?php echo $modId; ?>" class="<?php echo $moduleAttribs['class'] ?>" aria-labelledby="<?php echo $module->title; ?>">
			<div class="card-body"><?php echo $module->content; ?></div>
		</div>
	<?php endif; ?>
<?php endif; ?>
