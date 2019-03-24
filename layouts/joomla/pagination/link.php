<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Language\Text;

$item    = $displayData['data'];
$display = $item->text;

switch ((string) $item->text)
{
	// Check for "Start" item
	case Text::_('JLIB_HTML_START') :
		$icon = 'fa fa-angle-double-left';
		$aria = Text::sprintf('JLIB_HTML_GOTO_POSITION', strtolower($item->text));
		break;

	// Check for "Prev" item
	case $item->text === Text::_('JPREV') :
		$item->text = Text::_('JPREVIOUS');
		$icon = 'fa fa-angle-left';
		$aria =Text::sprintf('JLIB_HTML_GOTO_POSITION', strtolower($item->text));
		break;

	// Check for "Next" item
	case Text::_('JNEXT') :
		$icon = 'fa fa-angle-right';
		$aria = Text::sprintf('JLIB_HTML_GOTO_POSITION', strtolower($item->text));
		break;

	// Check for "End" item
	case Text::_('JLIB_HTML_END') :
		$icon = 'fa fa-angle-double-right';
		$aria = Text::sprintf('JLIB_HTML_GOTO_POSITION', strtolower($item->text));
		break;

	default:
		$icon = null;
		$aria = Text::sprintf('JLIB_HTML_GOTO_PAGE', strtolower($item->text));
		break;
}

if ($icon !== null)
{
	$display = '<span class="' . $icon . '" aria-hidden="true"></span>';
}

if ($displayData['active'])
{
	if ($item->base > 0)
	{
		$limit = 'limitstart.value=' . $item->base;
	}
	else
	{
		$limit = 'limitstart.value=0';
	}

	$class = 'active';
	$onClick = 'document.adminForm.' . $item->prefix . 'limitstart.value=' . ($item->base > 0 ? $item->base : '0') . '; Joomla.submitform();return false;';
}
else
{
	$class = (property_exists($item, 'active') && $item->active) ? 'active' : 'disabled';
}
?>
<?php if ($displayData['active']) : ?>
	<li class="<?php echo $class; ?> page-link">
		<a aria-label="<?php echo $aria; ?>" href="#" onclick="<?php echo $onClick; ?>">
			<?php echo $display; ?>
		</a>
	</li>
<?php elseif (isset($item->active) && $item->active) : ?>
	<?php $aria = Text::sprintf('JLIB_HTML_PAGE_CURRENT', strtolower($item->text)); ?>
	<li class="<?php echo $class; ?> page-link current">
		<span aria-current="true" aria-label="<?php echo $aria; ?>"><?php echo $display; ?></span>
	</li>
<?php else : ?>
	<li class="<?php echo $class; ?> page-link">
		<?php echo $display; ?>
	</li>
<?php endif; ?>
