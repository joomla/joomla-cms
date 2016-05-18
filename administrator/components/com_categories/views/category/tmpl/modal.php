<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'bottom'));
?>
<button id="applyBtn" type="button" class="hidden" onclick="Joomla.submitbutton('category.apply');"></button>
<button id="saveBtn" type="button" class="hidden" onclick="Joomla.submitbutton('category.save');"></button>
<button id="closeBtn" type="button" class="hidden" onclick="Joomla.submitbutton('category.cancel');"></button>

<div class="container-popup">
	<?php $this->setLayout('edit'); ?>
	<?php echo $this->loadTemplate(); ?>
</div>
