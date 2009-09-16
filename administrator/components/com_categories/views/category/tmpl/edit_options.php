<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div class="width-40">
		<legend><?php echo JText::_('Categories_Fieldset_Options');?></legend>
<?php foreach($this->form->getFields('params') as $field): ?>
	<?php if ($field->hidden): ?>
		<?php echo $field->input; ?>
	<?php else: ?>
		
				<?php echo $field->label; ?>
			
				<?php echo $field->input; ?>
		
	<?php endif; ?>
<?php endforeach; ?>
</div>