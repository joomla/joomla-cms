<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_finder
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_SITE . '/components/com_finder/helpers/html');

JHtml::_('bootstrap.tooltip');

// Load the smart search component language file.
$lang = JFactory::getLanguage();
$lang->load('com_finder', JPATH_SITE);

$suffix = $params->get('moduleclass_sfx');
$output = '<input type="text" name="q" class="js-finder-search-query input-medium" size="'
	. $params->get('field_size', 20) . '" value="' . htmlspecialchars(JFactory::getApplication()->input->get('q', '', 'string'), ENT_COMPAT, 'UTF-8') . '"'
	. ' placeholder="' . JText::_('MOD_FINDER_SEARCH_VALUE') . '">';

$showLabel  = $params->get('show_label', 1);
$labelClass = (!$showLabel ? 'element-invisible ' : '') . 'finder' . $suffix;
$label      = '<label for="mod-finder-searchword' . $module->id . '" class="' . $labelClass . '">' . $params->get('alt_label', JText::_('JSEARCH_FILTER_SUBMIT')) . '</label>';

switch ($params->get('label_pos', 'left'))
{
	case 'top' :
		$output = $label . '<br>' . $output;
		break;

	case 'bottom' :
		$output .= '<br>' . $label;
		break;

	case 'right' :
		$output .= $label;
		break;

	case 'left' :
	default :
		$output = $label . $output;
		break;
}

if ($params->get('show_button'))
{
	$button = '<button class="btn btn-primary hasTooltip ' . $suffix . ' finder' . $suffix . '" type="submit" title="' . JText::_('MOD_FINDER_SEARCH_BUTTON') . '"><span class="icon-search icon-white"></span>' . JText::_('JSEARCH_FILTER_SUBMIT') . '</button>';

	switch ($params->get('button_pos', 'left'))
	{
		case 'top' :
			$output = $button . '<br>' . $output;
			break;

		case 'bottom' :
			$output .= '<br>' . $button;
			break;

		case 'right' :
			$output .= $button;
			break;

		case 'left' :
		default :
			$output = $button . $output;
			break;
	}
}


JHtml::_('stylesheet', 'vendor/awesomplete/awesomplete.css', array('version' => 'auto', 'relative' => true));
JHtml::_('script', 'com_finder/finder.js', array('version' => 'auto', 'relative' => true));

JText::script('MOD_FINDER_SEARCH_VALUE', true);

/*
 * This segment of code sets up the autocompleter.
 */
if ($params->get('show_autosuggest', 1))
{
	JHtml::_('script', 'vendor/awesomplete/awesomplete.min.js', array('version' => 'auto', 'relative' => true));
	JFactory::getDocument()->addScriptOptions('finder-search', array('url' => JRoute::_('index.php?option=com_finder&task=suggestions.suggest&format=json&tmpl=component')));
}
?>

<form class="js-finder-searchform form-search" action="<?php echo JRoute::_($route); ?>" method="get">
	<div class="finder<?php echo $suffix; ?>">
		<?php
		// Show the form fields.
		echo $output;
		?>

		<?php $show_advanced = $params->get('show_advanced'); ?>
		<?php if ($show_advanced == 2) : ?>
			<br />
			<a href="<?php echo JRoute::_($route); ?>"><?php echo JText::_('COM_FINDER_ADVANCED_SEARCH'); ?></a>
		<?php elseif ($show_advanced == 1) : ?>
			<div class="js-finder-advanced">
				<?php echo JHtml::_('filter.select', $query, $params); ?>
			</div>
		<?php endif; ?>
		<?php echo modFinderHelper::getGetFields($route, (int) $params->get('set_itemid')); ?>
	</div>
</form>
