<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$item      = $displayData['data'];
$display   = $item->text;
$app       = Factory::getApplication();
$direction = $app->getLanguage()->isRtl() ? "Rtl" : null;

switch ((string) $item->text)
{
	// Check for "Start" item
	case Text::_('JLIB_HTML_START') :
		$icon = 'paginationStart' . $direction;
		$aria = Text::sprintf('JLIB_HTML_GOTO_POSITION', strtolower($item->text));
		break;

	// Check for "Prev" item
	case $item->text === Text::_('JPREV') :
		$item->text = Text::_('JPREVIOUS');
		$icon       = 'paginationPrev' . $direction;
		$aria       = Text::sprintf('JLIB_HTML_GOTO_POSITION', strtolower($item->text));
		break;

	// Check for "Next" item
	case Text::_('JNEXT') :
		$icon = 'paginationNext' . $direction;
		$aria = Text::sprintf('JLIB_HTML_GOTO_POSITION', strtolower($item->text));
		break;

	// Check for "End" item
	case Text::_('JLIB_HTML_END') :
		$icon = 'paginationEnd' . $direction;
		$aria = Text::sprintf('JLIB_HTML_GOTO_POSITION', strtolower($item->text));
		break;

	default:
		$icon = null;
		$aria = Text::sprintf('JLIB_HTML_GOTO_PAGE', strtolower($item->text));
		break;
}

if ($icon !== null)
{
	$display = LayoutHelper::render('joomla.icon.iconclass', ['icon' => $icon]);
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

	if ($app->isClient('administrator'))
	{
		$link = 'href="#" onclick="document.adminForm.' . $item->prefix . $limit . '; Joomla.submitform();return false;"';
	}
	elseif ($app->isClient('site'))
	{
		$link = 'href="' . $item->link . '"';
	}
}
else
{
	$class = (property_exists($item, 'active') && $item->active) ? 'active' : 'disabled';
}

?>
<?php if ($displayData['active']) : ?>
	<li class="page-item">
		<a aria-label="<?php echo $aria; ?>" <?php echo $link; ?> class="page-link">
			<?php echo $display; ?>
		</a>
	</li>
<?php elseif (isset($item->active) && $item->active) : ?>
	<?php $aria = Text::sprintf('JLIB_HTML_PAGE_CURRENT', strtolower($item->text)); ?>
	<li class="<?php echo $class; ?> page-item">
		<span aria-current="true" aria-label="<?php echo $aria; ?>" class="page-link"><?php echo $display; ?></span>
	</li>
<?php else : ?>
	<li class="<?php echo $class; ?> page-item">
		<span class="page-link" aria-hidden="true"><?php echo $display; ?></span>
	</li>
<?php endif; ?>
