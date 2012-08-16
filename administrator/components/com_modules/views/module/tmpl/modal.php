<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div class="btn-toolbar">
	<div class="btn-group">
		<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('module.save');">
		<?php echo JText::_('JSAVE');?></button>
	</div>
	<div class="btn-group">
		<button type="button" class="btn" onclick="window.parent.SqueezeBox.close();">
		<?php echo JText::_('JCANCEL');?></button>
	</div>
	<div class="clearfix"></div>
</div>

<?php
$this->setLayout('edit');
echo $this->loadTemplate();
