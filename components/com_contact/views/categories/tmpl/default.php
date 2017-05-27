<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
JHtml::_('behavior.caption');

JFactory::getDocument()->addScriptDeclaration("
jQuery(function($) {
	$('.categories-list').find('[id^=category-btn-]').each(function(index, btn) {
		var btn = $(btn);
		btn.on('click', function() {
			btn.find('span').toggleClass('icon-plus');
			btn.find('span').toggleClass('icon-minus');
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
