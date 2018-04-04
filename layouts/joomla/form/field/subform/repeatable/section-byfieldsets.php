<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Make thing clear
 *
 * @var JForm   $form       The form instance for render the section
 * @var string  $basegroup  The base group name
 * @var string  $group      Current group name
 * @var array   $buttons    Array of the buttons that will be rendered
 */
extract($displayData);
?>

<div class="subform-repeatable-group" data-base-name="<?php echo $basegroup; ?>" data-group="<?php echo $group; ?>">
	<?php if (!empty($buttons)) : ?>
	<div class="btn-toolbar text-right">
		<div class="btn-group">
			<?php if (!empty($buttons['add'])) : ?><a class="group-add btn btn-sm button btn-success" aria-label="<?php echo Text::_('JGLOBAL_FIELD_ADD'); ?>"><span class="fa fa-plus icon-white" aria-hidden="true"></span> </a><?php endif; ?>
			<?php if (!empty($buttons['remove'])) : ?><a class="group-remove btn btn-sm button btn-danger" aria-label="<?php echo Text::_('JGLOBAL_FIELD_REMOVE'); ?>"><span class="fa fa-minus icon-white" aria-hidden="true"></span> </a><?php endif; ?>
			<?php if (!empty($buttons['move'])) : ?><a class="group-move btn btn-sm button btn-primary" aria-label="<?php echo Text::_('JGLOBAL_FIELD_MOVE'); ?>"><span class="fa fa-arrows icon-white" aria-hidden="true"></span> </a><?php endif; ?>
		</div>
	</div>
	<?php endif; ?>
	<div class="row">
		<?php foreach ($form->getFieldsets() as $fieldset) : ?>
		<fieldset class="<?php if (!empty($fieldset->class)){ echo $fieldset->class; } ?>">
			<?php if (!empty($fieldset->label)) : ?>
				<legend><?php echo Text::_($fieldset->label); ?></legend>
			<?php endif; ?>
			<?php foreach ($form->getFieldset($fieldset->name) as $field) : ?>
				<?php echo $field->renderField(); ?>
			<?php endforeach; ?>
		</fieldset>
		<?php endforeach; ?>
	</div>
</div>
