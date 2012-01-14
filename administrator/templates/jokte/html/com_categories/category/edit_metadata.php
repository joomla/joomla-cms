<?php
/**
 * @version		$Id: edit_metadata.php 20196 2011-01-09 02:40:25Z ian $
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<ol class="adminformlist">
	<li><?php echo $this->form->getLabel('metadesc'); ?>
	<?php echo $this->form->getInput('metadesc'); ?></li>

	<li><?php echo $this->form->getLabel('metakey'); ?>
	<?php echo $this->form->getInput('metakey'); ?></li>

<?php foreach($this->form->getGroup('metadata') as $field): ?>
	<?php if ($field->hidden): ?>
		<li><?php echo $field->input; ?></li>
	<?php else: ?>
		<li><?php echo $field->label; ?>
		<?php echo $field->input; ?></li>
	<?php endif; ?>
<?php endforeach; ?>
</ol>
