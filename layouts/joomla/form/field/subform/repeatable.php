<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
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
	JHtml::_('jquery.ui', array('core', 'sortable'));
	JHtml::_('script', 'system/subform-repeatable.js', array('version' => 'auto', 'relative' => true));
}

$sublayout = empty($groupByFieldset) ? 'section' : 'section-byfieldsets';
?>

<div class="row-fluid">
	<div class="subform-repeatable-wrapper subform-layout">
		<div class="subform-repeatable"
			data-bt-add="a.group-add-<?php echo $unique_subform_id; ?>"
			data-bt-remove="a.group-remove-<?php echo $unique_subform_id; ?>"
			data-bt-move="a.group-move-<?php echo $unique_subform_id; ?>"
			data-repeatable-element="div.subform-repeatable-group-<?php echo $unique_subform_id; ?>"
			data-minimum="<?php echo $min; ?>" data-maximum="<?php echo $max; ?>"
		>

			<?php if (!empty($buttons['add'])) : ?>
			<div class="btn-toolbar">
				<div class="btn-group">
					<a class="btn btn-mini button btn-success group-add group-add-<?php echo $unique_subform_id; ?>" aria-label="<?php echo JText::_('JGLOBAL_FIELD_ADD'); ?>">
						<span class="icon-plus" aria-hidden="true"></span>
					</a>
				</div>
			</div>
			<?php endif; ?>
		<?php
		foreach ($forms as $k => $form) :
			echo $this->sublayout(
				$sublayout,
				array(
					'form' => $form,
					'basegroup' => $fieldname,
					'group' => $fieldname . $k,
					'buttons' => $buttons,
					'unique_subform_id' => $unique_subform_id,
				)
			);
		endforeach;
		?>
		<?php if ($multiple) : ?>
			<template class="subform-repeatable-template-section" style="display: none;"><?php
				// Use str_replace to escape HTML in a simple way, it need for IE compatibility, and should be removed later
				echo str_replace(
						array('<', '>'),
						array('SUBFORMLT', 'SUBFORMGT'),
						trim(
							$this->sublayout(
								$sublayout,
								array(
									'form' => $tmpl,
									'basegroup' => $fieldname,
									'group' => $fieldname . 'X',
									'buttons' => $buttons,
									'unique_subform_id' => $unique_subform_id,
								)
							)
						)
				);
				?></template>
		<?php endif; ?>
		</div>
	</div>
</div>
