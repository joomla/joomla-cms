<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<fieldset class="form-horizontal">
	<legend><?php echo JText::_('COM_CONFIG_TEXT_FILTER_SETTINGS');?></legend>
	<p>
		<?php echo JText::_('COM_CONFIG_TEXT_FILTERS_DESC');?>
	</p>
	<div>
		<?php echo $this->form->getInput('filters');?>
	</div>
</fieldset>