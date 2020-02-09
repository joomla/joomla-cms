<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

extract($displayData);

$form = $forms[0];

$formfields = $form->getGroup('');

foreach ($formfields as $formfield)
{
	if ($formfield->type === 'Media')
	{
		$formfield->authorField = $field->authorField;
		$formfield->directory = $field->directory;
		$formfield->height = $field->height;
		$formfield->link = $field->link;
		$formfield->preview = $field->preview;
		$formfield->previewHeight = $field->previewHeight;
		$formfield->previewWidth = $field->previewWidth;
		$formfield->width = $field->width;
	}
	
	foreach ($displayData as $key => $value)
	{
		switch ($key)
		{
			case 'value':
				break;

			default:
				$formfield->$key = $value;
		}
	}
}
?>

<div class="subform-wrapper">
<?php foreach ($formfields as $field) : ?>
	<?php echo $field->renderField(); ?>
<?php endforeach; ?>
</div>
