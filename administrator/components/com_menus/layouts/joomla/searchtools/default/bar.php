<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/** @var  array  $displayData */
$data = $displayData;

if ($data['view'] instanceof \Joomla\Component\Menus\Administrator\View\Items\Html)
{
	// We will get the menutype filter & remove it from the form filters
	$menuTypeField = $data['view']->filterForm->getField('menutype');

	// Add the client selector before the form filters.
	$clientIdField = $data['view']->filterForm->getField('client_id');

	if ($clientIdField): ?>
	<div class="js-stools-container-selector">
		<div class="js-stools-field-selector js-stools-client_id">
			<?php echo $clientIdField->input; ?>
		</div>
	</div>
	<?php endif; ?>
	<div class="js-stools-container-selector">
		<div class="js-stools-field-selector js-stools-menutype">
			<?php echo $menuTypeField->input; ?>
		</div>
	</div>
	<?php
}
elseif ($data['view'] instanceof \Joomla\Component\Menus\Administrator\View\Menus\Html)
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
