<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
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

<?php if ($this->item->created_time) : ?>
	<?php echo $this->form->getLabel('created_time'); ?>
	<?php echo $this->form->getInput('created_time'); ?>
<?php endif; ?>

<?php if ($this->item->modified_time) : ?>
	<?php echo $this->form->getLabel('modified_time'); ?>
	<?php echo $this->form->getInput('modified_time'); ?>
<?php endif; ?>
