<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_config
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
?>

<div class="width-40">

<fieldset class="adminform">
	<legend><?php echo JText::_('Cookie Settings'); ?></legend>
	
			<?php
			foreach ($this->form->getFields('cookie') as $field):
			?>
					<?php echo $field->label; ?>

					<?php echo $field->input; ?>

			<?php
			endforeach;
			?>

</fieldset>
</div>