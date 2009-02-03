<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_members
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die('Restricted Access');
?>

<fieldset id="members-profile-core">
	<legend>
		<?php echo JText::_('Memebers_Profile_Core_Legend'); ?>
	</legend>
	<dl>
		<dt>
			<?php echo JText::_('Members_Profile_Name'); ?>
		</dt>
		<dd>
			<?php echo $this->data->name; ?>
		</dd>
		<dt>
			<?php echo JText::_('Members_Profile_Username'); ?>
		</dt>
		<dd>
			<?php echo $this->data->username; ?>
		</dd>
		<dt>
			<?php echo JText::_('Members_Profile_Registered_Date'); ?>
		</dt>
		<dd>
			<?php echo JHtml::date($this->data->registerDate); ?>
		</dd>
	</dl>
</fieldset>
