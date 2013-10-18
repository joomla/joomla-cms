<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$data = $displayData;

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : array();

if (is_array($data['options']))
{
	$data['options'] = new JRegistry($data['options']);
}

// Set some basic options
$data['options']->set('filtersApplied', !empty($data['view']->activeFilters));
$data['options']->set('defaultLimit', JFactory::getApplication()->getCfg('list_limit', 20));
$data['options']->set('formSelector', $data['options']->get('formSelector', '#adminForm'));

// Load the jQuery plugin && CSS
JHtml::_('script', 'jui/jquery.searchtools.js', false, true, false, false);
JHtml::_('stylesheet', 'jui/jquery.searchtools.css', false, true);

$doc = JFactory::getDocument();
$script = "
	(function($){
		$(document).ready(function() {
			$('" . $data['options']->get('formSelector', '#adminForm') . "').searchtools(
				" . $data['options']->toString() . "
			);
		});
	})(jQuery);
";
$doc->addScriptDeclaration($script);

?>
<div class="stools js-stools clearfix">
	<div id="filter-bar" class="clearfix">
		<div class="stools-bar">
			<?php echo $this->sublayout('bar', $data); ?>
		</div>
		<div class="hidden-phone hidden-tablet stools-list js-stools-container-order">
			<?php echo $this->sublayout('list', $data); ?>
		</div>
	</div>
	<!-- Filters div -->
	<div class="js-stools-container clearfix">
		<div class="js-stools-container-filter stools-filters hidden-phone">
			<?php echo $this->sublayout('filters', $data); ?>
		</div>
	</div>
</div>