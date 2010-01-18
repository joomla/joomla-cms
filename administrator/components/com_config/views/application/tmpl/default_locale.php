<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_config
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
?>
<div class="width-100">
<fieldset class="adminform">
	<legend><?php echo JText::_('LOCALE_SETTINGS'); ?></legend>
			<?php
			foreach ($this->form->getFields('locale') as $field):
			?>

					<?php echo $field->label; ?>

					<?php echo $field->input; ?>

			<?php
			endforeach;
			?>

</fieldset>
</div>