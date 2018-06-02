<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
JHtml::_('behavior.caption');
JHtml::_('behavior.core');

// Add strings for translations in Javascript.
JText::script('JGLOBAL_EXPAND_CATEGORIES');
JText::script('JGLOBAL_COLLAPSE_CATEGORIES');

JFactory::getDocument()->addScriptDeclaration("
jQuery(function($) {
	$('.categories-list').find('[id^=category-btn-]').each(function(index, btn) {
		var btn = $(btn);
		btn.on('click', function() {
			btn.find('span').toggleClass('icon-plus');
			btn.find('span').toggleClass('icon-minus');
			if (btn.attr('aria-label') === Joomla.JText._('JGLOBAL_EXPAND_CATEGORIES'))
			{
				btn.attr('aria-label', Joomla.JText._('JGLOBAL_COLLAPSE_CATEGORIES'));
			} else {
				btn.attr('aria-label', Joomla.JText._('JGLOBAL_EXPAND_CATEGORIES'));
			}		
		});
	});
});");
?>
<div class="categories-list<?php echo $this->pageclass_sfx; ?>">
	<?php
		echo JLayoutHelper::render('joomla.content.categories_default', $this);
		echo $this->loadTemplate('items');
	?>
</div>
