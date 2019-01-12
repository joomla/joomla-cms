<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/** @var  array  $displayData */
$data = $displayData;

if ($data['view'] instanceof MenusViewItems)
{
	// We will get the menutype filter & remove it from the form filters
	$menuTypeField = $data['view']->filterForm->getField('menutype');

	// Add the client selector before the form filters.
	$clientIdField = $data['view']->filterForm->getField('client_id');

	if ($clientIdField): ?>
	<div class="js-stools-field-filter js-stools-client_id">
		<?php echo $clientIdField->input; ?>
	</div>
	<?php endif; ?>

	<div class="js-stools-field-filter js-stools-menutype">
		<?php echo $menuTypeField->input; ?>
	</div>
	<?php
}
elseif ($data['view'] instanceof MenusViewMenus)
{
	// Add the client selector before the form filters.
	$clientIdField = $data['view']->filterForm->getField('client_id');
	?>
	<div class="js-stools-field-filter js-stools-client_id">
		<?php echo $clientIdField->input; ?>
	</div>
	<?php
}

// Display the main joomla layout
echo JLayoutHelper::render('joomla.searchtools.default.bar', $data, null, array('component' => 'none'));
