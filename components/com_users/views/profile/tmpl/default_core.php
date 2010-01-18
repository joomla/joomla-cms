<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.user.helper');
?>

<fieldset id="users-profile-core">
	<legend>
		<?php echo JText::_('Users_Profile_Core_Legend'); ?>
	</legend>
	<dl>
		<dt>
			<?php echo JText::_('Users_Profile_Name_Label'); ?>
		</dt>
		<dd>
			<?php echo $this->data->name; ?>
		</dd>
		<dt>
			<?php echo JText::_('Users_Profile_Username_Label'); ?>
		</dt>
		<dd>
			<?php echo $this->data->username; ?>
		</dd>
		<dt>
			<?php echo JText::_('Users_Profile_Registered_Date_Label'); ?>
		</dt>
		<dd>
			<?php echo JHtml::date($this->data->registerDate); ?>
		</dd>
				<dt>
			<?php echo JText::_('Users_Profile_Last_Visited_Date_Label'); ?>
		</dt>
		<dd>
		<?php if ($this->data->lastvisitDate != '0000-00-00 00:00:00'){?>		
			<?php echo JHtml::date($this->data->lastvisitDate); ?>
		<?php }	
		 else {		
			echo JText::_('Users_Profile_Never_Visited');
			 } ?>	
		</dd>
	</dl>
</fieldset>
