<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('JPATH_BASE') or die();

$form = $displayData->getForm();

// JLayout for standard handling of metadata fields in the administrator content edit screens.
$fieldSets = $form->getFieldsets('metadata');
?>

<?php foreach ($fieldSets as $name => $fieldSet) : ?>
	<?php if (isset($fieldSet->description) && trim($fieldSet->description)) : ?>
		<p class="alert alert-info"><?php echo $this->escape(JText::_($fieldSet->description)); ?></p>
	<?php endif; ?>

	<?php
	// Include the real fields in this panel.
	if ($name === 'jmetadata')
	{
		echo $form->renderField('metadesc');
		echo $form->renderField('metakey');
		echo $form->renderField('xreference');
	}

	foreach ($form->getFieldset($name) as $field)
	{
		if ($field->name !== 'jform[metadata][tags][]')
		{
			echo $field->renderField();
		}
	} ?>
<?php endforeach; ?>
