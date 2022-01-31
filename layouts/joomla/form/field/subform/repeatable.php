<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   Form    $tmpl             The Empty form for template
 * @var   array   $forms            Array of JForm instances for render the rows
 * @var   bool    $multiple         The multiple state for the form field
 * @var   int     $min              Count of minimum repeating in multiple mode
 * @var   int     $max              Count of maximum repeating in multiple mode
 * @var   string  $name             Name of the input field.
 * @var   string  $fieldname        The field name
 * @var   string  $fieldId          The field ID
 * @var   string  $control          The forms control
 * @var   string  $label            The field label
 * @var   string  $description      The field description
 * @var   string  $class            Classes for the container
 * @var   array   $buttons          Array of the buttons that will be rendered
 * @var   bool    $groupByFieldset  Whether group the subform fields by it`s fieldset
 */
if ($multiple)
{
	// Add script
	Factory::getApplication()
		->getDocument()
		->getWebAssetManager()
		->useScript('webcomponent.field-subform');
}

$class = $class ? ' ' . $class : '';

$sublayout = empty($groupByFieldset) ? 'section' : 'section-byfieldsets';
?>

<div class="subform-repeatable-wrapper subform-layout">
	<joomla-field-subform class="subform-repeatable<?php echo $class; ?>" name="<?php echo $name; ?>"
		button-add=".group-add" button-remove=".group-remove" button-move="<?php echo empty($buttons['move']) ? '' : '.group-move' ?>"
		repeatable-element=".subform-repeatable-group" minimum="<?php echo $min; ?>" maximum="<?php echo $max; ?>">
		<?php if (!empty($buttons['add'])) : ?>
		<div class="btn-toolbar">
			<div class="btn-group">
				<button type="button" class="group-add btn btn-sm button btn-success" aria-label="<?php echo Text::_('JGLOBAL_FIELD_ADD'); ?>">
					<span class="icon-plus icon-white" aria-hidden="true"></span>
				</button>
			</div>
		</div>
		<?php endif; ?>
	<?php
	foreach ($forms as $k => $form) :
		echo $this->sublayout($sublayout, array('form' => $form, 'basegroup' => $fieldname, 'group' => $fieldname . $k, 'buttons' => $buttons));
	endforeach;
	?>
	<?php if ($multiple) : ?>
	<template class="subform-repeatable-template-section hidden"><?php
		echo trim($this->sublayout($sublayout, array('form' => $tmpl, 'basegroup' => $fieldname, 'group' => $fieldname . 'X', 'buttons' => $buttons)));
	?></template>
	<?php endif; ?>
	</joomla-field-subform>
</div>
