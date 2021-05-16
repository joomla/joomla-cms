<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * html5 (chosen html5 tag and font header tags)
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

$module  = $displayData['module'];
$params  = $displayData['params'];

if ((string) $module->content === '')
{
	return;
}

$moduleTag              = htmlspecialchars($params->get('module_tag', 'div'), ENT_QUOTES, 'UTF-8');
$moduleAttribs          = [];
$moduleAttribs['class'] = 'moduletable ' . htmlspecialchars($params->get('moduleclass_sfx'), ENT_QUOTES, 'UTF-8');
$bootstrapSize          = (int) $params->get('bootstrap_size', 0);
$moduleAttribs['class'] .= $bootstrapSize !== 0 ? ' col-md-' . $bootstrapSize : '';
$headerTag              = htmlspecialchars($params->get('header_tag', 'h3'), ENT_QUOTES, 'UTF-8');
$headerClass            = htmlspecialchars($params->get('header_class', ''), ENT_QUOTES, 'UTF-8');
$headerAttribs          = [];

// Only output a header class if one is set
if ($headerClass !== '')
{
	$headerAttribs['class'] = $headerClass;
}

// Only add aria if the moduleTag is not a div
if ($moduleTag !== 'div')
{
	if ($module->showtitle) :
		$moduleAttribs['aria-labelledby'] = 'mod-' . $module->id;
		$headerAttribs['id']              = 'mod-' . $module->id;
	else:
		$moduleAttribs['aria-label'] = $module->title;
	endif;
}

$header = '<' . $headerTag . ' ' . ArrayHelper::toString($headerAttribs) . '>' . $module->title . '</' . $headerTag . '>';
?>
<<?php echo $moduleTag; ?> <?php echo ArrayHelper::toString($moduleAttribs); ?>>
	<?php if ((bool) $module->showtitle) : ?>
		<?php echo $header; ?>
	<?php endif; ?>
	<?php echo $module->content; ?>
</<?php echo $moduleTag; ?>>
