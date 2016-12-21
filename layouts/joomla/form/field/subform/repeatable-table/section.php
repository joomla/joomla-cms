<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Make thing clear
 *
 * @var JForm   $form       The form instance for render the section
 * @var string  $basegroup  The base group name
 * @var string  $group      Current group name
 * @var array   $buttons    Array of the buttons that will be rendered
 */
extract($displayData);

JFactory::getDocument()->addStyleDeclaration(
	'.subform-table-sublayout-section .controls { margin-left: 0px }'
);
?>

<tr class="subform-repeatable-group" data-base-name="<?php echo $basegroup; ?>" data-group="<?php echo $group; ?>">
	<?php foreach ($form->getGroup('') as $field) : ?>
	<td>
		<?php echo $field->renderField(array('hiddenLabel' => true)); ?>
	</td>
	<?php endforeach; ?>
	<?php if (!empty($buttons)) : ?>
	<td>
		<div class="btn-group">
			<?php if (!empty($buttons['add'])) : ?><a class="group-add btn btn-mini button btn-success"><span class="icon-plus"></span> </a><?php endif; ?>
			<?php if (!empty($buttons['remove'])) : ?><a class="group-remove btn btn-mini button btn-danger"><span class="icon-minus"></span> </a><?php endif; ?>
			<?php if (!empty($buttons['move'])) : ?><a class="group-move btn btn-mini button btn-primary"><span class="icon-move"></span> </a><?php endif; ?>
		</div>
	</td>
	<?php endif; ?>
</tr>
