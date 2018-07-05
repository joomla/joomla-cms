<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_finder
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Module\Finder\Site\Helper\FinderHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::addIncludePath(JPATH_SITE . '/components/com_finder/helpers/html');

// Load the smart search component language file.
$lang = Factory::getLanguage();
$lang->load('com_finder', JPATH_SITE);

$input = '<input type="text" name="q" class="js-finder-search-query form-control" value="' . htmlspecialchars(Factory::getApplication()->input->get('q', '', 'string'), ENT_COMPAT, 'UTF-8') . '"'
	. ' placeholder="' . Text::_('MOD_FINDER_SEARCH_VALUE') . '">';

$showLabel  = $params->get('show_label', 1);
$labelClass = (!$showLabel ? 'sr-only ' : '') . 'finder';
$label      = '<label for="mod-finder-searchword' . $module->id . '" class="' . $labelClass . '">' . $params->get('alt_label', Text::_('JSEARCH_FILTER_SUBMIT')) . '</label>';

$output = '';

if ($params->get('show_button'))
{
	$output .= $label;
	$output .= '<div class="mod-finder__search input-group">';
	$output .= $input;
	$output .= '<span class="input-group-append">';
	$output .= '<button class="btn btn-primary hasTooltip" type="submit" title="' . Text::_('MOD_FINDER_SEARCH_BUTTON') . '"><span class="fa fa-search icon-white" aria-hidden="true"></span> ' . Text::_('JSEARCH_FILTER_SUBMIT') . '</button>';
	$output .= '</span>';
	$output .= '</div>';
}
else
{
	$output .= $label;
	$output .= $input;
}

HTMLHelper::_('stylesheet', 'vendor/awesomplete/awesomplete.css', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('script', 'com_finder/finder.js', array('version' => 'auto', 'relative' => true));

Text::script('MOD_FINDER_SEARCH_VALUE', true);

/*
 * This segment of code sets up the autocompleter.
 */
if ($params->get('show_autosuggest', 1))
{
	HTMLHelper::_('script', 'vendor/awesomplete/awesomplete.min.js', array('version' => 'auto', 'relative' => true));
	Factory::getDocument()->addScriptOptions('finder-search', array('url' => Route::_('index.php?option=com_finder&task=suggestions.suggest&format=json&tmpl=component')));
}
?>

<form class="mod-finder js-finder-searchform form-search" action="<?php echo Route::_($route); ?>" method="get">
	<?php echo $output; ?>

	<?php $show_advanced = $params->get('show_advanced'); ?>
	<?php if ($show_advanced == 2) : ?>
		<br>
		<a href="<?php echo Route::_($route); ?>" class="mod-finder__advanced-link"><?php echo Text::_('COM_FINDER_ADVANCED_SEARCH'); ?></a>
	<?php elseif ($show_advanced == 1) : ?>
		<div class="mod-finder__advanced js-finder-advanced">
			<?php echo HTMLHelper::_('filter.select', $query, $params); ?>
		</div>
	<?php endif; ?>
	<?php echo FinderHelper::getGetFields($route, (int) $params->get('set_itemid')); ?>
</form>
