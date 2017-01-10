<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$data = $displayData;

if ($data['view'] instanceof AssociationsViewAssociations)
{
	$app = JFactory::getApplication();

	// We will get the component item type and language filters & remove it from the form filters.
	if ($app->input->get('forcedItemType', '', 'string') == '')
	{
		$itemTypeField = $data['view']->filterForm->getField('itemtype');
?>
	<div class="js-stools-field-filter js-stools-selector">
		<?php echo $itemTypeField->input; ?>
	</div>
<?php
	}
	if ($app->input->get('forcedLanguage', '', 'cmd') == '')
	{
		$languageField = $data['view']->filterForm->getField('language');
?>
	<div class="js-stools-field-filter js-stools-selector">
		<?php echo $languageField->input; ?>
	</div>
<?php
	}
}

// Display the main joomla layout
echo JLayoutHelper::render('joomla.searchtools.default.bar', $data, null, array('component' => 'none'));
