<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<fieldset id="users-profile-custom">
	<legend>
		<?php echo JText::_('Members_Profile_Custom_Legend'); ?>
	</legend>
	<dl>
	<?php
	foreach($this->form->getFields('profile') as $field):
		if (!$field->hidden) :
	?>
		<dt><?php echo $field->label; ?></dt>
		<dd>
			<?php echo isset($this->profile[$field->name]) ? $this->profile[$field->name] : JText::_('Users_Profile_Value_Not_Found'); ?>
		</dd>
	<?php
		endif;
	endforeach;
	?>
	</dl>
</fieldset>