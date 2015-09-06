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
 * @var array $forms - array of JForm for render
 * @var bool  $multiple
 * @var int   $max - maximum repeatin for multiple
 * @var string $fieldname - the field name
 * @var string $control - the forms control
 * @var string $label
 * @var string $description
 */
extract($displayData);

$form = $forms[0];
?>

<!-- Render fields for subform "<?php echo $fieldname; ?>" -->
<div class="subform-wrapper">
<legend class="hasTooltip" title="<?php echo JHtml::tooltipText($label, $description); ?>" >
	<?php echo $label; ?>
</legend>
<?php foreach($form->getGroup('') as $field): ?>
	<?php echo $field->renderField(); ?>
<?php endforeach; ?>
</div>
