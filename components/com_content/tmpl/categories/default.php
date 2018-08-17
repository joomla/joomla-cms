<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers');
HTMLHelper::_('behavior.core');

// Add strings for translations in Javascript.
Text::script('JGLOBAL_EXPAND_CATEGORIES');
Text::script('JGLOBAL_COLLAPSE_CATEGORIES');

$js = <<<JS
(function() {
	document.addEventListener('DOMContentLoaded', function() {
		var categories = [].slice.call(document.querySelectorAll('.categories-list'));

		categories.forEach(function(category) {
			var buttons = [].slice.call(document.querySelectorAll('.categories-list'));

			buttons.forEach(function(button) {
				var span = button.querySelector('span');

				if(span) {
				  span.classList.toggle('icon-plus')
				  span.classList.toggle('icon-minus')
				}

				if (button.getAttribute('aria-label') === Joomla.JText._('JGLOBAL_EXPAND_CATEGORIES'))
				{
					button.setAttribute('aria-label', Joomla.JText._('JGLOBAL_COLLAPSE_CATEGORIES'));
				} else {
					button.setAttribute('aria-label', Joomla.JText._('JGLOBAL_EXPAND_CATEGORIES'));
				}
			})
	  })
	});
})();
JS;

// @todo move script to a file
Factory::getDocument()->addScriptDeclaration($js);
?>
<div class="com-content-categories categories-list">
	<?php
		echo LayoutHelper::render('joomla.content.categories_default', $this);
		echo $this->loadTemplate('items');
	?>
</div>
