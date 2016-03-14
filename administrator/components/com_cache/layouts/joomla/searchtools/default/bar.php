<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$data = $displayData;

if ($data['view'] instanceof CacheViewCache)
{
	$clientIdField = $data['view']->filterForm->getField('client_id');
?>
	<div class="js-stools-field-filter js-stools-client_id">
		<?php echo $clientIdField->input; ?>
	</div>
<?php
}

// Display the main joomla layout
echo JLayoutHelper::render('joomla.searchtools.default.bar', $data, null, array('component' => 'none'));
