<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<button id="applyBtn" type="button" class="hidden" onclick="Joomla.submitbutton('plugin.apply');"></button>
<button id="saveBtn" type="button" class="hidden" onclick="Joomla.submitbutton('plugin.save');"></button>
<button id="closeBtn" type="button" class="hidden" onclick="Joomla.submitbutton('plugin.cancel');"></button>

<div class="container-popup">
	<?php $this->setLayout('edit'); ?>
	<?php echo $this->loadTemplate(); ?>
</div>

