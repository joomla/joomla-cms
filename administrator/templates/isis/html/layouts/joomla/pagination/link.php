<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/** @var JPaginationObject $item */
$item = $displayData['data'];

if (!empty($displayData['pagOptions']))
{
	$options = new Joomla\Registry\Registry($displayData['pagOptions']);
	$liClass = $options->get('liClass', '');
	$addText = $options->get('addText', '');
}
else
{
	$liClass = $addText = '';
}

$display = $item->text;

switch ((string) $item->text)
{
	// Check for "Start" item
	case JText::_('JLIB_HTML_START') :
		$icon = 'icon-backward icon-first';
		break;

	// Check for "Prev" item
	case JText::_('JPREV') :
		$item->text = JText::_('JPREVIOUS');
		$icon = 'icon-step-backward icon-previous';
		break;

	// Check for "Next" item
	case JText::_('JNEXT') :
		$icon = 'icon-step-forward icon-next';
		break;

	// Check for "End" item
	case JText::_('JLIB_HTML_END') :
		$icon = 'icon-forward icon-last';
		break;

	default:
		$icon = null;
		break;
}

$item->text .= $addText ? $addText : '';

if ($icon !== null)
{
	$display = '<span class="' . $icon . '"></span>';
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

	$cssClasses = array();

	$title = '';

	if (!is_numeric($item->text))
	{
		JHtml::_('bootstrap.tooltip');
		$cssClasses[] = 'hasTooltip';
		$title = ' title="' . $item->text . '" ';
	}

	$onClick = 'document.adminForm.' . $item->prefix . 'limitstart.value=' . ($item->base > 0 ? $item->base : '0') . '; Joomla.submitform();return false;';
}
else
{
	$class = (property_exists($item, 'active') && $item->active) ? 'active' : 'disabled';
	if ($class != 'active')
	{
		$class .= $liClass ? ($class ? ' ' : '') . $liClass : '';
	}
}
?>
<?php if ($displayData['active']) : ?>
	<li<?php echo $liClass ? ' class="' . $liClass . '"' : ''; ?>>
		<a class="<?php echo implode(' ', $cssClasses); ?>" <?php echo $title; ?> href="#" onclick="<?php echo $onClick; ?>">
			<?php echo $display; ?>
		</a>
	</li>
<?php else : ?>
	<li class="<?php echo $class; ?>">
		<span><?php echo $display; ?></span>
	</li>
<?php endif;
