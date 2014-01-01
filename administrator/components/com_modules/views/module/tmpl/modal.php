<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_modules
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
?>
<div class="fltrt">
	<button type="button" onclick="Joomla.submitbutton('module.save');">
		<?php echo JText::_('JSAVE');?></button>
	<button type="button" onclick="window.parent.SqueezeBox.close();">
		<?php echo JText::_('JCANCEL');?></button>
</div>
<div class="clr"></div>

<?php
$this->setLayout('edit');
echo $this->loadTemplate();
