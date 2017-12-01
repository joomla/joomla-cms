<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;

// @deprecated 4.0 the function parameter, the inline js and the buttons are not needed since 3.7.0.
$function  = Factory::getApplication()->input->getCmd('function', 'jEditCategory_' . (int) $this->item->id);

// Pass the function name from PHP to Javascript
Factory::getDocument()->addScriptOptions('categoryEdit', ['name' => $this->escape($function)]);

HTMLHelper::_('script', 'media/com_categories/admin-category-modal.min.js', ['relative' => true, 'version' => 'auto']);
?>
<button id="applyBtn" type="button" class="hidden" onclick="Joomla.submitbutton('category.apply'); Joomla.jEditCategoryModal();"></button>
<button id="saveBtn" type="button" class="hidden" onclick="Joomla.submitbutton('category.save'); Joomla.jEditCategoryModal();"></button>
<button id="closeBtn" type="button" class="hidden" onclick="Joomla.submitbutton('category.cancel');"></button>

<div class="container-popup">
	<?php $this->setLayout('edit'); ?>
	<?php echo $this->loadTemplate(); ?>
</div>
