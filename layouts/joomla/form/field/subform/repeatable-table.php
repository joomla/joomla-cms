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
 * @var JForm $tmpl - Empty form for template
 * @var array $forms - array of JForm for render
 * @var bool  $multiple
 * @var int   $min - minimum repeatin for multiple
 * @var int   $max - maximum repeatin for multiple
 * @var string $fieldname - the field name
 * @var string $control - the forms control
 * @var string $label
 * @var string $description
 * @var array  $buttons Buttons that will be displied
 * @var bool   $groupByFieldset Whether group subform fields by its fieldset
 */
extract($displayData);

// Add script
if($multiple)
{
	JHtml::_('jquery.ui', array('core', 'sortable'));
	JHtml::_('script', 'system/subform-repeatable.js', false, true);
}

// Build heading
$table_head = '';
if(!empty($groupByFieldset))
{
	foreach($tmpl->getFieldsets() as $fieldset) {
		$table_head .= '<th>' . JText::_($fieldset->label);
		if(!empty($fieldset->description))
		{
			$table_head .= '<br /><small style="font-weight:normal">' . JText::_($fieldset->description) . '</small>';
		}
		$table_head .= '</th>';
	}
	$sublayout = 'section-byfieldsets';
}
else
{
	foreach($tmpl->getGroup('') as $field) {
		$table_head .= '<th>' . strip_tags($field->label);
		$table_head .= '<br /><small style="font-weight:normal">' . JText::_($field->description) . '</small>';
		$table_head .= '</th>';
	}
	$sublayout = 'section';
}

?>

<div class="row-fluid">
	<!-- Render fields for repeatable subform "<?php echo $fieldname; ?>" -->
	<div class="subform-repeatable-wrapper subform-table-layout">
		<div class="subform-repeatable"
			data-bt-add="a.group-add" data-bt-remove="a.group-remove" data-bt-move="a.group-move"
			data-repeatable-element="tr.subform-repeatable-group"
			data-rows-container="tbody" data-minimum="<?php echo $min; ?>" data-maximum="<?php echo $max; ?>">

		<table class="adminlist table table-striped table-bordered">
			<thead>
				<tr>
					<?php echo $table_head; ?>
					<?php if(!empty($buttons)):?>
					<th style="width:8%;">
					<?php if(!empty($buttons['add'])):?>
						<div class="btn-group">
							<a class="group-add btn btn-mini button btn-success"><span class="icon-plus"></span> </a>
						</div>
					<?php endif;?>
					</th>
					<?php endif; ?>
				</tr>
			</thead>
			<tbody>
			<?php
			foreach($forms as $k => $form):
				echo $this->sublayout($sublayout, array('form' => $form, 'basegroup' => $fieldname, 'group' => $fieldname . $k, 'buttons' => $buttons));
			endforeach;
			?>
			</tbody>
		</table>
		<?php if($multiple):?>
		<!-- Template subform "<?php echo $fieldname; ?>" -->
		<script type="text/subform-repeatable-template-section" class="subform-repeatable-template-section">
		<?php echo $this->sublayout($sublayout, array('form' => $tmpl, 'basegroup' => $fieldname, 'group' => $fieldname . 0, 'buttons' => $buttons));?>
		</script>
		<!-- End template subform "<?php echo $fieldname; ?>" -->
		<?php endif;?>
		</div>
	</div>
	<!-- End render fields for repeatable subform "<?php echo $fieldname; ?>" -->
</div>
