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

<fieldset id="members-profile-custom">
	<legend>
		<?php echo JText::_('Memebers_Profile_Custom_Legend'); ?>
	</legend>
	<dl>
	<?php
	foreach($this->form->getFields('profile') as $field):
	?>
		<dt><?php echo $field->label; ?></dt>
		<dd><?php echo $this->profile[$field->name]; ?></dd>
	<?php
	endforeach;
	?>
	</dl>
</fieldset>