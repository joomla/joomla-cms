<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Make thing clear
 *
 * subfield
 * @var JForm     $layout           The Empty form for template
 * @var array     $formsourc        Array of JForm instances for render the rows
 * @var int       $min              Count of minimum repeating in multiple mode
 * @var int       $max              Count of maximum repeating in multiple mode
 * @var array     $buttons          Array of the buttons that will be rendered
 * @var bool      $groupByFieldset  Whether group the subform fields by it`s fieldset
 * media
 * @var  string   $asset            The asset text
 * @var  string   $authorField      The label text
 * @var  string   $directory        The folder text
 * @var  string   $link             The link text
 * @var  string   $preview          The preview image relative path
 * @var  integer  $height           The image height
 * @var  integer  $width            The image width
 * @var  integer  $previewHeight    The image preview height
 * @var  integer  $previewWidth     The image preview width
 */
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
