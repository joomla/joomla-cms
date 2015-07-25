<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Make thing clear
 *
 * @var JForm $tmpl - Empry form for template
 * @var array $forms - array of ZForm for render
 * @var bool  $multiple
 * @var int   $min - minimum repeatin for multiple
 * @var int   $max - maximum repeatin for multiple
 * @var string $fieldname - the field name
 * @var string $control - the forms control
 * @var string $label
 * @var string $description
 * @var array  $buttons Buttons that will be enabled
 * @var bool   $groupByFieldset Whether group subform fields by its fieldset
 */
extract($displayData);

// Add script
JHtml::_('jquery.ui', array('core', 'sortable'));
JHtml::_('script', 'system/subform-repeatable.js', false, true);

$sublayout = empty($groupByFieldset) ? 'section' : 'section-byfieldsets';
?>

<div class="row-fluid">
	<!-- Render fields for repeatable subform "<?php echo $fieldname; ?>" -->
	<div class="subform-repeatable-wrapper subform-layout">
		<legend class="hasTooltip" title="<?php echo JHtml::tooltipText($label, $description); ?>" >
			<?php echo $label; ?>
		</legend>
		<div class="subform-repeatable"
			data-bt-add="a.group-add" data-bt-remove="a.group-remove" data-bt-move="a.group-move"
			data-repeatable-element="div.subform-repeatable-group" data-minimum="<?php echo $min; ?>" data-maximum="<?php echo $max; ?>">
			<div class="btn-toolbar text-right">
				<div class="btn-group">
					<?php if(!empty($buttons['add'])):?>
					<a class="group-add btn btn-mini button btn-success"><span class="icon-plus"></span> </a>
					<?php endif; ?>
				</div>
			</div>
		<?php
		foreach($forms as $k => $form):
			echo $this->sublayout($sublayout, array('form' => $form, 'basegroup' => $fieldname, 'group' => $fieldname . $k, 'buttons' => $buttons));
		endforeach;
		?>
		<?php if($multiple):?>
		<!-- Template subform "<?php echo $fieldname; ?>" -->
		<script type="text/subform-repeatable-template-section" class="subform-repeatable-template-section">
		<?php echo $this->sublayout($sublayout, array('form' => $tmpl, 'basegroup' => $fieldname, 'group' => $fieldname . 0, 'buttons' => $buttons))?>
		</script>
		<!-- End template subform "<?php echo $fieldname; ?>" -->
		<?php endif;?>
		</div>
	</div>
	<!-- End render fields for repeatable subform "<?php echo $fieldname; ?>" -->
</div>
