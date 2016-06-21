<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'bottom'));
?>
<button class="hidden" id="applyBtn" type="button" onclick="Joomla.submitbutton('user.apply');"></button>
<button class="hidden" id="saveBtn" type="button" onclick="Joomla.submitbutton('user.save');"></button>
<button class="hidden" id="closeBtn" type="button" onclick="Joomla.submitbutton('user.cancel');"></button>

<div class="container-popup">
	<?php $this->setLayout('edit'); ?>
	<?php echo $this->loadTemplate(); ?>
</div>
