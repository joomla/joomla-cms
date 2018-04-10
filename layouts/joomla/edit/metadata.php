<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Language\Text;

$form = $displayData->getForm();

// JLayout for standard handling of metadata fields in the administrator content edit screens.
$fieldSets = $form->getFieldsets('metadata');
?>

<?php foreach ($fieldSets as $name => $fieldSet) : ?>
	<?php if (isset($fieldSet->description) && trim($fieldSet->description)) : ?>
		<joomla-alert type="info"><?php echo $this->escape(Text::_($fieldSet->description)); ?></joomla-alert>
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
