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
$moduleAttribs          = [];
$moduleAttribs['class'] = $module->position . ' card card-grey ' . htmlspecialchars($params->get('moduleclass_sfx'), ENT_QUOTES, 'UTF-8');
$headerTag              = htmlspecialchars($params->get('header_tag', 'h4'), ENT_QUOTES, 'UTF-8');
$headerClass            = htmlspecialchars($params->get('header_class', ''), ENT_QUOTES, 'UTF-8');
$headerAttribs          = [];
$headerAttribs['class'] = $headerClass;

if ($module->showtitle) :
	$moduleAttribs['aria-labelledby'] = 'mod-' . $module->id;
	$headerAttribs['id']             = 'mod-' . $module->id;

	if ($headerClass !== 'card-title') :
		$headerAttribs['class'] .= 'card-header ' . $headerClass;
	endif;
else:
	$moduleAttribs['aria-label'] = $module->title;
endif;

$header = '<' . $headerTag . ' ' . ArrayHelper::toString($headerAttribs) . '>' . $module->title . '</' . $headerTag . '>';
?>
<<?php echo $moduleTag; ?> <?php echo ArrayHelper::toString($moduleAttribs); ?>>
	<?php if ($module->showtitle && $headerClass !== 'card-title') : ?>
		<?php echo $header; ?>
	<?php endif; ?>
	<div class="card-body">
		<?php if ($module->showtitle && $headerClass === 'card-title') : ?>
			<?php echo $header; ?>
		<?php endif; ?>
		<?php echo $module->content; ?>
	</div>
</<?php echo $moduleTag; ?>>
