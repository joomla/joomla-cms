<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Make thing clear
 *
 * @var JForm   $tmpl             The Empty form for template
 * @var array   $forms            Array of JForm instances for render the rows
 * @var bool    $multiple         The multiple state for the form field
 * @var int     $min              Count of minimum repeating in multiple mode
 * @var int     $max              Count of maximum repeating in multiple mode
 * @var string  $fieldname        The field name
 * @var string  $control          The forms control
 * @var string  $label            The field label
 * @var string  $description      The field description
 * @var array   $buttons          Array of the buttons that will be rendered
 * @var bool    $groupByFieldset  Whether group the subform fields by it`s fieldset
 */
extract($displayData);

// Add script
if ($multiple)
{
	//JHtml::_('jquery.ui', array('core', 'sortable'));
	//JHtml::_('script', 'system/fields/subform-repeatable.min.js', array('version' => 'auto', 'relative' => true));
	JHtml::_('webcomponent', ['joomla-field-subform' => 'system/webcomponents/joomla-field-subform.min.js'], ['relative' => true, 'version' => 'auto', 'detectBrowser' => false, 'detectDebug' => true]);
}

// Build heading
$table_head = '';

if (!empty($groupByFieldset))
{
	foreach ($tmpl->getFieldsets() as $fieldset) {
		$table_head .= '<th>' . JText::_($fieldset->label);

		if (!empty($fieldset->description))
		{
			$table_head .= '<br><small style="font-weight:normal">' . JText::_($fieldset->description) . '</small>';
		}

		$table_head .= '</th>';
	}

	$sublayout = 'section-byfieldsets';
}
else
{
	foreach ($tmpl->getGroup('') as $field) {
		$table_head .= '<th>' . strip_tags($field->label);
		$table_head .= '<br><small style="font-weight:normal">' . JText::_($field->description) . '</small>';
		$table_head .= '</th>';
	}

	$sublayout = 'section';

	// Label will not be shown for sections layout, so reset the margin left
	JFactory::getDocument()->addStyleDeclaration(
		'.subform-table-sublayout-section .controls { margin-left: 0px }'
	);
}
?>

<div class="row">
	<div class="subform-repeatable-wrapper subform-table-layout subform-table-sublayout-<?php echo $sublayout; ?>">
		<joomla-field-subform class="subform-repeatable"
			button-add=".group-add" button-remove=".group-remove" button-move="<?php echo empty($buttons['move']) ? '' : '.group-move' ?>"
			repeatable-element=".subform-repeatable-group"
			rows-container="tbody" minimum="<?php echo $min; ?>" maximum="<?php echo $max; ?>">

		<table class="adminlist table table-striped table-bordered">
			<thead>
				<tr>
					<?php echo $table_head; ?>
					<?php if (!empty($buttons)) : ?>
					<th style="width:8%;">
					<?php if (!empty($buttons['add'])) : ?>
						<div class="btn-group">
							<a class="group-add btn btn-sm button btn-success" aria-label="<?php echo JText::_('JGLOBAL_FIELD_ADD'); ?>"><span class="icon-plus icon-white" aria-hidden="true"></span> </a>
						</div>
					<?php endif; ?>
					</th>
					<?php endif; ?>
				</tr>
			</thead>
			<tbody>
			<?php
			foreach ($forms as $k => $form) :
				echo $this->sublayout($sublayout, array('form' => $form, 'basegroup' => $fieldname, 'group' => $fieldname . $k, 'buttons' => $buttons));
			endforeach;
			?>
			</tbody>
		</table>
		<?php if ($multiple) : ?>
		<template class="subform-repeatable-template-section" style="display: none;">
		<?php echo $this->sublayout($sublayout, array('form' => $tmpl, 'basegroup' => $fieldname, 'group' => $fieldname . 'X', 'buttons' => $buttons)); ?>
		</template>
		<?php endif; ?>
		</joomla-field-subform>
	</div>
</div>
