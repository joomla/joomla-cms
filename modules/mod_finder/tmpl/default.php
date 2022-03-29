<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_SITE . '/components/com_finder/helpers/html');

JHtml::_('jquery.framework');
JHtml::_('formbehavior.chosen');
JHtml::_('bootstrap.tooltip');

// Load the smart search component language file.
$lang = JFactory::getLanguage();
$lang->load('com_finder', JPATH_SITE);

$suffix = $params->get('moduleclass_sfx');
$output = '<input type="text" name="q" id="mod-finder-searchword' . $module->id . '" class="search-query input-medium" size="'
	. $params->get('field_size', 20) . '" value="' . htmlspecialchars(JFactory::getApplication()->input->get('q', '', 'string'), ENT_COMPAT, 'UTF-8') . '"'
	. ' placeholder="' . JText::_('MOD_FINDER_SEARCH_VALUE') . '"/>';

$showLabel  = $params->get('show_label', 1);
$labelClass = (!$showLabel ? 'element-invisible ' : '') . 'finder' . $suffix;
$label      = '<label for="mod-finder-searchword' . $module->id . '" class="' . $labelClass . '">' . $params->get('alt_label', JText::_('JSEARCH_FILTER_SUBMIT')) . '</label>';

switch ($params->get('label_pos', 'left'))
{
	case 'top' :
		$output = $label . '<br />' . $output;
		break;

	case 'bottom' :
		$output .= '<br />' . $label;
		break;

	case 'right' :
		$output .= $label;
		break;

	case 'left' :
	default :
		$output = $label . $output;
		break;
}

if ($params->get('show_button', 0))
{
	$button = '<button class="btn btn-primary hasTooltip ' . $suffix . ' finder' . $suffix . '" type="submit" title="' . JText::_('MOD_FINDER_SEARCH_BUTTON') . '"><span class="icon-search icon-white"></span>' . JText::_('JSEARCH_FILTER_SUBMIT') . '</button>';

	switch ($params->get('button_pos', 'left'))
	{
		case 'top' :
			$output = $button . '<br />' . $output;
			break;

		case 'bottom' :
			$output .= '<br />' . $button;
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

JHtml::_('stylesheet', 'com_finder/finder.css', array('version' => 'auto', 'relative' => true));

$script = "
jQuery(document).ready(function() {
	var value, searchword = jQuery('#mod-finder-searchword" . $module->id . "');

		// Get the current value.
		value = searchword.val();

		// If the current value equals the default value, clear it.
		searchword.on('focus', function ()
		{
			var el = jQuery(this);

			if (el.val() === '" . JText::_('MOD_FINDER_SEARCH_VALUE', true) . "')
			{
				el.val('');
			}
		});

		// If the current value is empty, set the previous value.
		searchword.on('blur', function ()
		{
			var el = jQuery(this);

			if (!el.val())
			{
				el.val(value);
			}
		});

		jQuery('#mod-finder-searchform" . $module->id . "').on('submit', function (e)
		{
			e.stopPropagation();
			var advanced = jQuery('#mod-finder-advanced" . $module->id . "');

			// Disable select boxes with no value selected.
			if (advanced.length)
			{
				advanced.find('select').each(function (index, el)
				{
					var el = jQuery(el);

					if (!el.val())
					{
						el.attr('disabled', 'disabled');
					}
				});
			}
		});";
/*
 * This segment of code sets up the autocompleter.
 */
if ($params->get('show_autosuggest', 1))
{
	JHtml::_('script', 'jui/jquery.autocomplete.min.js', array('version' => 'auto', 'relative' => true));

	$script .= "
	var suggest = jQuery('#mod-finder-searchword" . $module->id . "').autocomplete({
		serviceUrl: '" . JRoute::_('index.php?option=com_finder&task=suggestions.suggest&format=json&tmpl=component') . "',
		paramName: 'q',
		minChars: 1,
		maxHeight: 400,
		width: 300,
		zIndex: 9999,
		deferRequestBy: 500
	});";
}

$script .= '});';

JFactory::getDocument()->addScriptDeclaration($script);
?>

<div class="finder<?php echo $suffix; ?>">
	<form id="mod-finder-searchform<?php echo $module->id; ?>" action="<?php echo JRoute::_($route); ?>" method="get" class="form-search" role="search">
		<?php
		// Show the form fields.
		echo $output;
		?>

		<?php $show_advanced = $params->get('show_advanced', 0); ?>
		<?php if ($show_advanced == 2) : ?>
			<br />
			<a href="<?php echo JRoute::_($route); ?>"><?php echo JText::_('COM_FINDER_ADVANCED_SEARCH'); ?></a>
		<?php elseif ($show_advanced == 1) : ?>
			<div id="mod-finder-advanced<?php echo $module->id; ?>">
				<?php echo JHtml::_('filter.select', $query, $params); ?>
			</div>
		<?php endif; ?>
		<?php echo modFinderHelper::getGetFields($route, (int) $params->get('set_itemid', 0)); ?>
	</form>
</div>
