<?php
/**
 * @version		$Id: edit_metadata.php 17342 2010-05-29 06:15:59Z eddieajau $
 * @package		Joomla.Administrator
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<?php echo $this->form->getLabel('metadesc'); ?>
<?php echo $this->form->getInput('metadesc'); ?>

<?php echo $this->form->getLabel('metakey'); ?>
<?php echo $this->form->getInput('metakey'); ?>

<?php foreach($this->form->getGroup('metadata') as $field): ?>
	<?php if ($field->hidden): ?>
		<?php echo $field->input; ?>
	<?php else: ?>
		<?php echo $field->label; ?>
		<?php echo $field->input; ?>
	<?php endif; ?>
<?php endforeach; ?>
